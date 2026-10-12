<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;
use ZipArchive;

class PembacaExcelPkl
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public function baca(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages([
                'file_excel' => 'Fitur baca Excel memerlukan ekstensi PHP Zip.',
            ]);
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages([
                'file_excel' => 'File Excel tidak dapat dibuka.',
            ]);
        }

        try {
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            if ($sheetXml === false || $workbookXml === false) {
                throw ValidationException::withMessages([
                    'file_excel' => 'Workbook Excel tidak memiliki sheet yang dapat dibaca.',
                ]);
            }

            $sharedStrings = $this->sharedStrings($zip);
            $sheet = simplexml_load_string($sheetXml, SimpleXMLElement::class, LIBXML_NONET);
            if (! $sheet) {
                throw ValidationException::withMessages([
                    'file_excel' => 'Isi sheet Excel tidak valid.',
                ]);
            }

            $sheet->registerXPathNamespace('x', self::NS);
            $rows = [];
            $header = [];
            $rowNumber = 0;
            foreach ($sheet->xpath('/x:worksheet/x:sheetData/x:row') ?: [] as $row) {
                $row->registerXPathNamespace('x', self::NS);
                $rowNumber = (int) $row['r'];
                $values = [];
                foreach ($row->xpath('./x:c') ?: [] as $cell) {
                    $column = preg_replace('/\d+/', '', (string) $cell['r']);
                    $index = $this->columnIndex($column);
                    $values[$index] = $this->cellValue($cell, $sharedStrings);
                }
                if ($rowNumber === 1) {
                    foreach ($values as $index => $name) {
                        $header[$index] = Str::lower(trim((string) $name));
                    }
                    continue;
                }
                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }
                $kelas = trim((string) $this->valueFor($values, $header, 'kelas'));
                $jurusan = trim((string) $this->valueFor($values, $header, 'jurusan'));
                $rows[] = [
                    'baris' => $rowNumber,
                    'nis' => trim((string) ($this->valueFor($values, $header, 'nisn')
                        ?: $this->valueFor($values, $header, 'nis'))),
                    'nama' => trim((string) $this->valueFor($values, $header, 'nama_lengkap')),
                    'kelas' => $kelas,
                    'jurusan' => $jurusan !== '' ? $jurusan : $this->jurusanDariKelas($kelas),
                    'industri' => trim((string) $this->valueFor($values, $header, 'industri')),
                ];
                if (count($rows) > 1000) {
                    throw ValidationException::withMessages([
                        'file_excel' => 'Maksimal 1.000 siswa dalam satu kali impor.',
                    ]);
                }
            }

            foreach (['nama_lengkap', 'kelas', 'industri'] as $required) {
                if (! in_array($required, $header, true)) {
                    throw ValidationException::withMessages([
                        'file_excel' => 'Kolom wajib tidak ditemukan: '.$required.'.',
                    ]);
                }
            }
            if (! in_array('nisn', $header, true) && ! in_array('nis', $header, true)) {
                throw ValidationException::withMessages([
                    'file_excel' => 'Kolom wajib tidak ditemukan: nis atau nisn.',
                ]);
            }

            if ($rows === []) {
                throw ValidationException::withMessages([
                    'file_excel' => 'Tidak ada data siswa di dalam file.',
                ]);
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }
        $strings = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET);
        if (! $strings) {
            return [];
        }

        $strings->registerXPathNamespace('x', self::NS);
        $result = [];
        foreach ($strings->xpath('/x:sst/x:si') ?: [] as $item) {
            $item->registerXPathNamespace('x', self::NS);
            $result[] = implode('', array_map('strval', $item->xpath('.//x:t') ?: []));
        }

        return $result;
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        if ((string) $cell['t'] === 'inlineStr') {
            $cell->registerXPathNamespace('x', self::NS);
            return implode('', array_map('strval', $cell->xpath('./x:is//x:t') ?: []));
        }
        $cell->registerXPathNamespace('x', self::NS);
        $value = (string) (($cell->xpath('./x:v')[0] ?? null) ?? '');
        if ((string) $cell['t'] === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }
        return $value;
    }

    private function valueFor(array $values, array $header, string $name): string
    {
        $index = array_search($name, $header, true);
        return $index === false ? '' : (string) ($values[$index] ?? '');
    }

    private function jurusanDariKelas(string $kelas): string
    {
        if (preg_match('/^(?:10|11|12|X|XI|XII)\s+(.+?)(?:\s+\d+[A-Z]?)?$/i', trim($kelas), $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private function columnIndex(string $column): int
    {
        $index = 0;
        foreach (str_split($column) as $letter) {
            $index = $index * 26 + (ord(strtoupper($letter)) - 64);
        }
        return $index - 1;
    }
}
