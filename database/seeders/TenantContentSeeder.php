<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Campus;
use App\Models\Event;
use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Page;
use App\Models\Sermon;
use App\Models\SermonSeries;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantContentSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = tenant() ?? Tenant::first();

        if (!$tenant) {
            $this->command->error('No tenant found. Run tenant:create first.');
            return;
        }

        if (!tenant()) {
            tenancy()->initialize($tenant);
        }

        $this->command->info("Seeding realistic content for tenant: {$tenant->id}");

        $campus = Campus::first() ?? Campus::factory()->create(['name' => 'Campus Principal', 'tenant_id' => $tenant->id]);

        $this->seedSermonSeries($tenant);
        $this->seedSermons($tenant, $campus);
        $this->seedEvents($tenant, $campus);
        $this->seedAnnouncements($tenant);
        $this->seedMembers($tenant);
        $this->seedPages($tenant);
        $this->seedGivingRecords($tenant);

        $this->command->info('Tenant content seeded successfully with real-world examples.');
    }

    private function seedSermonSeries(Tenant $tenant): void
    {
        $series = [
            ['title' => 'Les Fondements de la Foi', 'description' => 'Une étude approfondie des bases de la vie chrétienne.'],
            ['title' => 'Le Pouvoir du Saint-Esprit', 'description' => 'Découvrir comment marcher dans la puissance de l\'Esprit.'],
            ['title' => 'Bâtir des Familles Solides', 'description' => 'Conseils bibliques pour les mariages et l\'éducation des enfants.'],
            ['title' => 'La Vision 2026', 'description' => 'Comprendre où Dieu conduit notre église cette année.'],
        ];

        foreach ($series as $data) {
            SermonSeries::factory()->create(array_merge($data, ['tenant_id' => $tenant->id]));
        }

        $this->command->info('  - 4 sermon series created');
    }

    private function seedSermons(Tenant $tenant, Campus $campus): void
    {
        $seriesIds = SermonSeries::pluck('id')->toArray();
        $sermons = [
            ['title' => 'La puissance de la prière matinale', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'Vaincre la peur par la foi', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'L\'amour inconditionnel : Leçon du fils prodigue', 'speaker' => 'Diacre Marie-Claire Adou'],
            ['title' => 'L\'intégrité dans le service', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'Vivre une vie de gratitude', 'speaker' => 'Évangéliste Paul Mensah'],
            ['title' => 'La grâce qui transforme les coeurs', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'L\'unité : Notre force commune', 'speaker' => 'Diacre Marie-Claire Adou'],
            ['title' => 'Le combat spirituel au quotidien', 'speaker' => 'Évangéliste Paul Mensah'],
            ['title' => 'La fidélité dans les petites choses', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'Le pardon : Chemin vers la liberté', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'Découvrir ses dons spirituels', 'speaker' => 'Pasteur Koffi Amoah'],
            ['title' => 'La sagesse de Salomon pour aujourd\'hui', 'speaker' => 'Pasteur Emmanuel Kofi'],
        ];

        foreach ($sermons as $index => $data) {
            Sermon::factory()->create(array_merge($data, [
                'tenant_id' => $tenant->id,
                'campus_id' => $campus->id,
                'series_id' => $seriesIds[array_rand($seriesIds)],
                'date' => now()->subWeeks($index),
                'duration' => random_int(2400, 4200),
            ]));
        }

        $this->command->info('  - 12 sermons created');
    }

    private function seedEvents(Tenant $tenant, Campus $campus): void
    {
        Event::factory()->upcoming()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'title' => 'Culte de Célébration Dominicale',
            'location' => 'Auditorium Principal',
            'start_at' => now()->next('Sunday')->setHour(9),
            'end_at' => now()->next('Sunday')->setHour(12),
            'description' => 'Un temps fort de louange, d\'adoration et d\'écoute de la Parole pour toute la famille.',
        ]);

        Event::factory()->upcoming()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'title' => 'Veillée d\'Intercession Nationale',
            'location' => 'Salle des Banquets',
            'start_at' => now()->next('Friday')->setHour(22),
            'end_at' => now()->next('Friday')->addDay()->setHour(5),
            'description' => 'Une nuit entière dédiée à la prière pour notre nation et nos familles.',
        ]);

        Event::factory()->upcoming()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'title' => 'Séminaire sur les Finances Bibliques',
            'location' => 'Annexe B',
            'start_at' => now()->addWeeks(2)->next('Saturday')->setHour(10),
            'end_at' => now()->addWeeks(2)->next('Saturday')->setHour(16),
            'capacity' => 150,
            'description' => 'Apprenez à gérer vos finances selon les principes du Royaume.',
        ]);

        Event::factory()->upcoming()->create([
            'tenant_id' => $tenant->id,
            'campus_id' => $campus->id,
            'title' => 'Retraite des Jeunes Impact 2026',
            'location' => 'Centre de Retraite Mont Carmel',
            'start_at' => now()->addMonths(1)->setHour(8),
            'end_at' => now()->addMonths(1)->addDays(3)->setHour(15),
            'capacity' => 100,
        ]);

        $this->command->info('  - 4 major events created');
    }

    private function seedAnnouncements(Tenant $tenant): void
    {
        Announcement::factory()->active()->pinned()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Lancement de l\'Ecole du Ministère',
            'body' => 'Les inscriptions pour la session d\'automne 2026 sont désormais ouvertes. Transformez votre appel en ministère.',
            'target_group' => 'leaders',
        ]);

        Announcement::factory()->active()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Nouvelle application mobile',
            'body' => 'Téléchargez notre nouvelle application pour suivre les sermons en direct et rester connecté.',
            'target_group' => 'all',
        ]);

        $this->command->info('  - 2 announcements created');
    }

    private function seedMembers(Tenant $tenant): void
    {
        Member::factory(30)->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        $this->command->info('  - 30 members created');
    }

    private function seedPages(Tenant $tenant): void
    {
        Page::factory()->published()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Qui sommes-nous ?',
            'slug' => 'a-propos',
            'content_blocks' => [
                ['type' => 'heading', 'data' => ['level' => 'h2', 'content' => 'Notre Vision']],
                ['type' => 'rich_text', 'data' => ['body' => 'Une église vibrante, centrée sur Christ, impactant les nations par l\'Evangile et l\'amour de Dieu.']],
            ],
        ]);

        $this->command->info('  - 1 static page created');
    }

    private function seedGivingRecords(Tenant $tenant): void
    {
        $members = Member::pluck('id')->toArray();
        for ($i = 0; $i < 40; $i++) {
            GivingRecord::factory()->create([
                'tenant_id' => $tenant->id,
                'member_id' => empty($members) ? null : $members[array_rand($members)],
                'amount' => random_int(5000, 100000),
                'currency' => 'XOF',
                'date' => now()->subDays(random_int(0, 60)),
            ]);
        }

        $this->command->info('  - 40 giving records created');
    }
}
