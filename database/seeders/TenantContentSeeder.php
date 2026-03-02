<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\GivingRecord;
use App\Models\Member;
use App\Models\Page;
use App\Models\Sermon;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantContentSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = tenant() ?? Tenant::first();

        if (! $tenant) {
            $this->command->error('No tenant found. Run tenant:create first.');

            return;
        }

        if (! tenant()) {
            tenancy()->initialize($tenant);
        }

        $this->command->info("Seeding content for tenant: {$tenant->id}");

        $this->seedSermons($tenant);
        $this->seedEvents($tenant);
        $this->seedAnnouncements($tenant);
        $this->seedMembers($tenant);
        $this->seedPages($tenant);
        $this->seedGivingRecords($tenant);

        $this->command->info('Tenant content seeded successfully.');
    }

    private function seedSermons(Tenant $tenant): void
    {
        $sermons = [
            ['title' => 'La puissance de la prière', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'Marcher dans la foi', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'L\'amour inconditionnel de Dieu', 'speaker' => 'Diacre Marie-Claire Adou'],
            ['title' => 'Les bénédictions de l\'obéissance', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'Vivre par l\'Esprit', 'speaker' => 'Évangéliste Paul Mensah'],
            ['title' => 'La grâce qui transforme', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'L\'unité dans le corps de Christ', 'speaker' => 'Diacre Marie-Claire Adou'],
            ['title' => 'Le combat spirituel', 'speaker' => 'Évangéliste Paul Mensah'],
            ['title' => 'La fidélité de Dieu', 'speaker' => 'Pasteur Emmanuel Kofi'],
            ['title' => 'Le pardon libérateur', 'speaker' => 'Pasteur Emmanuel Kofi'],
        ];

        foreach ($sermons as $index => $data) {
            Sermon::factory()->create(array_merge($data, [
                'tenant_id' => $tenant->id,
                'date' => now()->subWeeks($index),
                'duration' => random_int(2400, 4200),
            ]));
        }

        $this->command->info('  - 10 sermons created');
    }

    private function seedEvents(Tenant $tenant): void
    {
        Event::factory()->upcoming()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Culte du dimanche',
            'location' => 'Salle principale',
            'start_at' => now()->next('Sunday')->setHour(9),
            'end_at' => now()->next('Sunday')->setHour(12),
            'description' => 'Rejoignez-nous pour notre culte dominical avec louange, prière et enseignement de la Parole.',
        ]);

        Event::factory()->upcoming()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Veillée de prière',
            'location' => 'Salle de prière',
            'start_at' => now()->next('Friday')->setHour(21),
            'end_at' => now()->next('Friday')->addDay()->setHour(5),
            'description' => 'Nuit de prière et d\'intercession pour notre communauté et notre nation.',
        ]);

        Event::factory()->upcoming()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Conférence des femmes 2026',
            'location' => 'Centre de conférences',
            'start_at' => now()->addMonth()->setHour(8),
            'end_at' => now()->addMonth()->setHour(17),
            'capacity' => 200,
            'description' => 'Une journée de formation et d\'édification pour les femmes de l\'église.',
        ]);

        Event::factory()->upcoming()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Camp de jeunes',
            'location' => 'Camp Shalom, Abidjan',
            'start_at' => now()->addMonths(2)->setHour(8),
            'end_at' => now()->addMonths(2)->addDays(3)->setHour(16),
            'capacity' => 100,
        ]);

        Event::factory()->past()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Baptême dans l\'eau',
            'location' => 'Plage de Lomé',
        ]);

        $this->command->info('  - 5 events created');
    }

    private function seedAnnouncements(Tenant $tenant): void
    {
        Announcement::factory()->active()->pinned()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Inscriptions ouvertes pour le camp de jeunes',
            'body' => 'Les inscriptions pour le camp de jeunes 2026 sont ouvertes. Places limitées à 100 personnes. Contactez le secrétariat pour vous inscrire.',
            'target_group' => 'youth',
        ]);

        Announcement::factory()->active()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Changement d\'horaire du culte',
            'body' => 'À partir du mois prochain, le culte du dimanche commencera à 9h au lieu de 10h.',
            'target_group' => 'all',
        ]);

        Announcement::factory()->active()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Réunion du conseil pastoral',
            'body' => 'La prochaine réunion du conseil pastoral aura lieu le samedi à 15h.',
            'target_group' => 'leaders',
        ]);

        Announcement::factory()->expired()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Collecte de Noël terminée',
            'body' => 'Merci à tous pour votre générosité. La collecte de Noël a permis de récolter 2 millions XOF.',
        ]);

        $this->command->info('  - 4 announcements created');
    }

    private function seedMembers(Tenant $tenant): void
    {
        $members = [];

        // Active members
        $members[] = Member::factory()->active()->create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Emmanuel',
            'last_name' => 'Kofi',
            'email' => 'pasteur@rehoboth.church',
            'status' => 'active',
        ]);

        $members[] = Member::factory()->active()->create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Marie-Claire',
            'last_name' => 'Adou',
            'email' => 'marieclaire@rehoboth.church',
            'status' => 'active',
        ]);

        // Random active members
        $randomMembers = Member::factory(18)->active()->create([
            'tenant_id' => $tenant->id,
        ]);

        $members = array_merge($members, $randomMembers->all());

        // Visiting members
        Member::factory(5)->visiting()->create([
            'tenant_id' => $tenant->id,
        ]);

        $this->command->info('  - 25 members created');

        // Create giving records for some members
        $this->seedMemberGivingRecords($tenant, $members);
    }

    private function seedPages(Tenant $tenant): void
    {
        Page::factory()->published()->withBlocks()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Accueil',
            'slug' => 'accueil',
            'seo_title' => 'Rehoboth Ministry International — Bienvenue',
            'seo_description' => 'Bienvenue à Rehoboth Ministry International, une communauté de foi vibrante au service de Dieu.',
        ]);

        Page::factory()->published()->create([
            'tenant_id' => $tenant->id,
            'title' => 'À propos',
            'slug' => 'a-propos',
            'content_blocks' => [
                [
                    'type' => 'heading',
                    'data' => ['level' => 'h2', 'content' => 'Notre histoire'],
                ],
                [
                    'type' => 'rich_text',
                    'data' => ['body' => 'Rehoboth Ministry International a été fondée en 2005 avec la vision de toucher les nations avec l\'Évangile de Jésus-Christ. Notre communauté est composée de croyants de divers horizons, unis par la foi et l\'amour de Dieu.'],
                ],
                [
                    'type' => 'heading',
                    'data' => ['level' => 'h2', 'content' => 'Notre vision'],
                ],
                [
                    'type' => 'rich_text',
                    'data' => ['body' => 'Former des disciples de Jésus-Christ qui transforment leur communauté et impactent le monde.'],
                ],
            ],
            'seo_title' => 'À propos — Rehoboth Ministry International',
        ]);

        Page::factory()->published()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Contact',
            'slug' => 'contact',
            'content_blocks' => [
                [
                    'type' => 'rich_text',
                    'data' => ['body' => 'Contactez-nous pour toute question ou pour nous rendre visite.'],
                ],
                [
                    'type' => 'call_to_action',
                    'data' => ['label' => 'Envoyez-nous un message', 'url' => 'mailto:info@rehoboth.church', 'style' => 'primary'],
                ],
            ],
        ]);

        Page::factory()->draft()->create([
            'tenant_id' => $tenant->id,
            'title' => 'Déclaration de foi',
            'slug' => 'declaration-de-foi',
        ]);

        $this->command->info('  - 4 pages created');
    }

    private function seedGivingRecords(Tenant $tenant): void
    {
        // Anonymous giving records
        GivingRecord::factory(10)->create([
            'tenant_id' => $tenant->id,
            'currency' => 'XOF',
        ]);

        $this->command->info('  - 10 anonymous giving records created');
    }

    private function seedMemberGivingRecords(Tenant $tenant, array $members): void
    {
        $givingMembers = array_slice($members, 0, 10);

        foreach ($givingMembers as $member) {
            GivingRecord::factory(random_int(1, 4))->create([
                'tenant_id' => $tenant->id,
                'member_id' => $member->id,
                'currency' => 'XOF',
            ]);
        }

        $this->command->info('  - Giving records linked to members');
    }
}
