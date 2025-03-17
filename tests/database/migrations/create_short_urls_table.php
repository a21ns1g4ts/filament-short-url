<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tabela principal de URLs encurtadas
        Schema::connection(config('short-url.connection'))->create('short_urls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('destination_url');

            // Chave única para a URL curta com collation específica para MySQL
            $table->string('url_key')->unique()->when(
                Schema::getConnection()->getConfig('driver') === 'mysql',
                function (Blueprint $column) {
                    $column->collation('utf8mb4_bin');
                }
            );

            $table->string('default_short_url');
            $table->boolean('single_use')->default(false);
            $table->boolean('forward_query_params')->default(false);
            $table->boolean('track_visits')->default(true);
            $table->integer('redirect_status_code')->default(301);

            // Configurações de rastreamento
            $table->boolean('track_ip_address')->default(false);
            $table->boolean('track_operating_system')->default(false);
            $table->boolean('track_operating_system_version')->default(false);
            $table->boolean('track_browser')->default(false);
            $table->boolean('track_browser_version')->default(false);
            $table->boolean('track_referer_url')->default(false);
            $table->boolean('track_device_type')->default(false);

            // Datas de ativação/desativação
            $table->timestamp('activated_at')->nullable()->useCurrent();
            $table->timestamp('deactivated_at')->nullable();

            $table->timestamps();
        });

        // Tabela de visitas às URLs encurtadas
        Schema::connection(config('short-url.connection'))->create('short_url_visits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('short_url_id');

            // Dados de rastreamento
            $table->string('ip_address')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('operating_system_version')->nullable();
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('referer_url')->nullable();
            $table->string('device_type')->nullable();

            $table->timestamp('visited_at')->useCurrent();
            $table->timestamps();

            // Chave estrangeira com cascade delete
            $table->foreign('short_url_id')
                ->references('id')
                ->on('short_urls')
                ->onDelete('cascade');
        });

        // Atualiza as configurações de rastreamento com base no arquivo de configuração
        DB::connection(config('short-url.connection'))->table('short_urls')->update([
            'track_ip_address' => config('short-url.tracking.fields.ip_address', false),
            'track_operating_system' => config('short-url.tracking.fields.operating_system', false),
            'track_operating_system_version' => config('short-url.tracking.fields.operating_system_version', false),
            'track_browser' => config('short-url.tracking.fields.browser', false),
            'track_browser_version' => config('short-url.tracking.fields.browser_version', false),
            'track_referer_url' => config('short-url.tracking.fields.referer_url', false),
            'track_device_type' => config('short-url.tracking.fields.device_type', false),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove as tabelas na ordem correta (primeiro a que tem FK)
        Schema::connection(config('short-url.connection'))->dropIfExists('short_url_visits');
        Schema::connection(config('short-url.connection'))->dropIfExists('short_urls');
    }
};
