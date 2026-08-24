<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Boilerplate d'export CSV partagé par les écrans d'export (clients,
 * statistiques, facturation) : en-têtes HTTP, BOM UTF-8, flux de sortie.
 * Le contenu (lignes, sections) reste propre à chaque export et est fourni
 * par le contrôleur via le callback $writer.
 */
final class CsvExportService
{
    /**
     * @param callable(resource): void $writer reçoit le flux ouvert et y écrit les lignes (fputcsv)
     */
    public function streamDownload(string $filename, callable $writer): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // BOM pour un affichage correct des accents dans Excel

        $writer($output);

        fclose($output);
        exit;
    }

    /**
     * Écrit une ligne CSV. Fixe explicitement le caractère d'échappement
     * (PHP 8.4 déprécie fputcsv() sans ce paramètre, la valeur par défaut
     * changera dans une version future).
     *
     * @param resource $handle
     */
    public function writeRow($handle, array $fields, string $separator = ',', string $enclosure = '"'): void
    {
        fputcsv($handle, $fields, $separator, $enclosure, '\\');
    }
}
