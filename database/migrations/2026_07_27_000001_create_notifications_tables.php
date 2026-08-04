<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('notification_lectures');
        Schema::dropIfExists('notifications');

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['echelon', 'bonification', 'digest_mensuel']);
            $table->string('titre', 200);
            $table->text('message')->nullable();

            // Notification individuelle (jour J) : liée à un personnel + un avancement précis.
            $table->foreignId('personnel_id')->nullable()->constrained('personnels')->cascadeOnDelete();
            $table->foreignId('avancement_id')->nullable()->constrained('avancements')->cascadeOnDelete();

            // Rappel mensuel (digest) : liste des personnes concernées.
            $table->json('payload')->nullable();

            $table->date('date_notification'); // date "logique" de la notification
            $table->timestamps();
        });

        Schema::create('notification_lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('lu_le')->useCurrent();

            $table->unique(['notification_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_lectures');
        Schema::dropIfExists('notifications');
    }
};
