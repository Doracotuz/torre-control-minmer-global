<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KpiGeneral;
use App\Models\KpiTiempo;

class ImportKpiCommand extends Command
{
    protected $signature = 'kpi:import {file} {type}';
    protected $description = 'Importa un archivo CSV de KPIs a la base de datos';

    public function handle()
    {
        $filePath = $this->argument('file');
        $type = $this->argument('type');

        $mapaGeneral = ['Año' => 'ano', 'Zona' => 'zona', 'Área' => 'area', 'Mes' => 'mes', 'Concepto' => 'concepto', 'Cantidad' => 'cantidad'];
        $mapaTiempo = ['Año' => 'ano', 'Zona' => 'zona', 'Área' => 'area', 'Mes' => 'mes', 'Concepto' => 'concepto', 'Porcentaje' => 'porcentaje'];
        
        $model = null; $mapa = [];
        if ($type === 'generales') { $model = new KpiGeneral(); $mapa = $mapaGeneral; } 
        elseif ($type === 'tiempo') { $model = new KpiTiempo(); $mapa = $mapaTiempo; } 
        else { $this->error("Tipo inválido."); return 1; }

        $model->truncate();
        
        if (($handle = fopen($filePath, 'r')) !== false) {
            $firstLine = fgets($handle);
            if (!$firstLine) {
                $this->error("El archivo CSV está vacío.");
                fclose($handle);
                return 1;
            }
            
            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            rewind($handle);

            $encodings_to_try = ['UTF-8', 'ISO-8859-1', 'Windows-1252'];

            $header_raw = fgetcsv($handle, 0, $delimiter);
            $header_utf8 = array_map(fn($h) => mb_convert_encoding($h, 'UTF-8', $encodings_to_try), $header_raw);
            $header = array_map('trim', $header_utf8);
            
            while (($row_raw = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (count($header) !== count($row_raw)) continue;
                
                $row = array_map(fn($r) => mb_convert_encoding($r, 'UTF-8', $encodings_to_try), $row_raw);

                $data = array_combine($header, $row);
                $datosParaBD = [];
                foreach($data as $key => $value) {
                    if(isset($mapa[$key])) {
                        $datosParaBD[$mapa[$key]] = $value;
                    }
                }

                if ($type === 'tiempo' && isset($datosParaBD['porcentaje'])) {
                    $datosParaBD['porcentaje'] = (float) str_replace('%', '', trim($datosParaBD['porcentaje']));
                }

                $model->create($datosParaBD);
            }
            fclose($handle);
        }
        $this->info("¡Archivo '{$type}' importado exitosamente!");
        return 0;
    }
}