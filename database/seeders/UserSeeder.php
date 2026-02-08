<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Crée les comptes de démonstration : admins, conducteurs et passagers.
     * Mot de passe commun pour la démo : password
     *
     * DEMO_MAIL_RECIPIENT est utilisé UNIQUEMENT ici, au moment du seed : il sert
     * uniquement à définir l'adresse email des 7 comptes démo (plus addressing :
     * toi+admin@gmail.com, toi+thomas@gmail.com, etc.). Cela n'affecte pas l'envoi
     * des mails : les mails partent vers l'adresse stockée en base (celle du compte).
     * La création de comptes normaux (inscription sur le site) n'est pas impactée :
     * les nouveaux utilisateurs gardent l'email qu'ils ont saisi et reçoivent les
     * mails sur cette adresse.
     */
    public function run(): void
    {
        $password = Hash::make('password');
        $baseEmail = config('app.demo_mail_recipient');

        $agenceParis = Agence::where('nom', 'Agence Paris Centre')->first();
        $agenceRennes = Agence::where('nom', 'Agence Rennes')->first();
        $agenceLyon = Agence::where('nom', 'Agence Lyon')->first();

        $userSpecs = [
            ['slug' => 'admin', 'name' => 'Admin Principal', 'agence_id' => null],
            ['slug' => 'admin2', 'name' => 'Marie Martin', 'agence_id' => $agenceParis?->id],
            ['slug' => 'thomas', 'name' => 'Thomas Dubois', 'agence_id' => $agenceParis?->id],
            ['slug' => 'lea', 'name' => 'Léa Petit', 'agence_id' => $agenceRennes?->id],
            ['slug' => 'julien', 'name' => 'Julien Moreau', 'agence_id' => $agenceRennes?->id],
            ['slug' => 'camille', 'name' => 'Camille Bernard', 'agence_id' => $agenceLyon?->id],
            ['slug' => 'lucas', 'name' => 'Lucas Simon', 'agence_id' => $agenceLyon?->id],
        ];

        $defaultEmails = [
            'admin' => 'admin@sparkotto.fr',
            'admin2' => 'admin2@sparkotto.fr',
            'thomas' => 'thomas.dubois@sparkotto.fr',
            'lea' => 'lea.petit@sparkotto.fr',
            'julien' => 'julien.moreau@sparkotto.fr',
            'camille' => 'camille.bernard@sparkotto.fr',
            'lucas' => 'lucas.simon@sparkotto.fr',
        ];

        foreach ($userSpecs as $spec) {
            $email = $this->buildDemoEmail($baseEmail, $spec['slug'], $defaultEmails[$spec['slug']]);
            $data = [
                'name' => $spec['name'],
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
                'agence_id' => $spec['agence_id'],
            ];
            User::firstOrCreate(
                ['email' => $email],
                $data
            );
        }
    }

    /**
     * Si baseEmail est défini (ex: toi@gmail.com), retourne toi+slug@gmail.com.
     * Sinon retourne l'email par défaut.
     */
    private function buildDemoEmail(?string $baseEmail, string $slug, string $defaultEmail): string
    {
        if (empty($baseEmail) || ! str_contains($baseEmail, '@')) {
            return $defaultEmail;
        }
        [$local, $domain] = explode('@', $baseEmail, 2);

        return $local.'+'.$slug.'@'.$domain;
    }
}
