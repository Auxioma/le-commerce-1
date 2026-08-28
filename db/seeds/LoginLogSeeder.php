<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `login_logs`, identifiants inclus.
 */
final class LoginLogSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `login_logs` LIMIT 1')) {
            return;
        }

        $this->table('login_logs')->insert([
            ['id' => 1, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', 'created_at' => '2026-08-19 17:27:01'],
            ['id' => 2, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:153.0) Gecko/20100101 Firefox/153.0', 'created_at' => '2026-08-19 21:42:17'],
            ['id' => 3, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-23 12:20:25'],
            ['id' => 4, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-24 08:28:38'],
            ['id' => 5, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-24 08:40:36'],
            ['id' => 6, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-24 10:06:30'],
            ['id' => 7, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-24 14:56:48'],
            ['id' => 8, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-24 14:58:42'],
            ['id' => 9, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-24 21:05:15'],
            ['id' => 10, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'curl/8.16.0', 'created_at' => '2026-08-24 21:24:47'],
            ['id' => 11, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-24 21:26:17'],
            ['id' => 12, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-26 11:11:39'],
            ['id' => 13, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-26 23:04:00'],
            ['id' => 14, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-27 22:44:01'],
            ['id' => 15, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-28 12:21:15'],
            ['id' => 16, 'user_id' => 5, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:154.0) Gecko/20100101 Firefox/154.0', 'created_at' => '2026-08-28 21:01:57'],
        ])->saveData();
    }
}
