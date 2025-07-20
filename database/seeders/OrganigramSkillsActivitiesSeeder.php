<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganigramSkillsActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        // 50 Habilidades (Skills)
        $skills = [
            ['name' => 'SAP CRM', 'description' => 'Gestión de clientes en SAP'],
            ['name' => 'Excel Intermedio', 'description' => 'Tablas dinámicas y fórmulas'],
            ['name' => 'WMS Básico', 'description' => 'Consultas de inventario'],
            ['name' => 'Comunicación Asertiva', 'description' => 'Email y llamadas efectivas'],
            ['name' => 'Técnicas de Servicio', 'description' => 'Protocolos atención al cliente'],
            ['name' => 'PowerPoint', 'description' => 'Presentaciones ejecutivas'],
            ['name' => 'Idioma Inglés', 'description' => 'Nivel B2 o superior'],
            ['name' => 'Manejo de Objeciones', 'description' => 'Técnicas comerciales'],
            ['name' => 'CRM Salesforce', 'description' => 'Gestión de casos'],
            ['name' => 'Escucha Activa', 'description' => 'Comprensión de necesidades'],
            
            ['name' => 'Design Thinking', 'description' => 'Metodología de innovación'],
            ['name' => 'Lean Six Sigma', 'description' => 'Certificación Green Belt'],
            ['name' => 'Power BI', 'description' => 'Dashboards avanzados'],
            ['name' => 'Python', 'description' => 'Automatización de procesos'],
            ['name' => 'Gestión Proyectos', 'description' => 'Metodologías ágiles'],
            ['name' => 'IoT Básico', 'description' => 'Dispositivos conectados'],
            ['name' => 'Blockchain', 'description' => 'Trazabilidad logística'],
            ['name' => 'Data Science', 'description' => 'Análisis predictivo'],
            ['name' => 'UI/UX', 'description' => 'Diseño de interfaces'],
            ['name' => 'ROI Analysis', 'description' => 'Evaluación financiera'],
            
            ['name' => 'Liderazgo', 'description' => 'Gestión de equipos'],
            ['name' => 'KPI Logísticos', 'description' => 'Interpretación de métricas'],
            ['name' => 'TMS Avanzado', 'description' => 'Sistemas de transporte'],
            ['name' => 'Resolución Crisis', 'description' => 'Manejo de contingencias'],
            ['name' => 'Normas OSHA', 'description' => 'Seguridad industrial'],
            ['name' => 'Costeo Logístico', 'description' => 'Análisis de costos'],
            ['name' => 'Negociación', 'description' => 'Acuerdos con proveedores'],
            ['name' => 'Cross-Functional', 'description' => 'Coordinación entre áreas'],
            ['name' => 'WMS Avanzado', 'description' => 'Administración de sistemas'],
            ['name' => 'Lean Logistics', 'description' => 'Reducción de desperdicios'],
            
            ['name' => 'ISO 9001', 'description' => 'Estándares de calidad'],
            ['name' => 'Checklists', 'description' => 'Elaboración de formatos'],
            ['name' => 'Documentación', 'description' => 'Control de embarques'],
            ['name' => 'Muestreo Estadístico', 'description' => 'Técnicas AQL'],
            ['name' => 'SAP MM', 'description' => 'Módulo de materiales'],
            ['name' => 'Normativas', 'description' => 'Regulaciones de transporte'],
            ['name' => 'Fotografía Evidencia', 'description' => 'Documentación visual'],
            ['name' => 'Análisis Root Cause', 'description' => 'Identificación de causas'],
            ['name' => 'Fraude Logístico', 'description' => 'Detección de anomalías'],
            ['name' => 'Embalajes', 'description' => 'Estándares de packaging'],
            
            ['name' => 'Ruteo Inteligente', 'description' => 'Optimización avanzada'],
            ['name' => 'Tableau', 'description' => 'Visualización de datos'],
            ['name' => 'SQL Avanzado', 'description' => 'Consultas complejas'],
            ['name' => 'Geolocalización', 'description' => 'Herramientas GIS'],
            ['name' => 'Simulación', 'description' => 'Modelos predictivos'],
            ['name' => 'Dock Scheduling', 'description' => 'Programación de muelles'],
            ['name' => 'Last Mile', 'description' => 'Estrategias urbanas'],
            ['name' => 'Costeo Transporte', 'description' => 'Análisis tarifario'],
            ['name' => 'Machine Learning', 'description' => 'Modelos logísticos'],
            ['name' => 'API Integration', 'description' => 'Conexión de sistemas']
        ];

        // 50 Actividades (Activities)
        $activities = [
            ['name' => 'Programación Citas', 'description' => 'Agendamiento de entregas'],
            ['name' => 'Seguimiento Entregas', 'description' => 'Tracking en tiempo real'],
            ['name' => 'Consulta Stock', 'description' => 'Disponibilidad en sistemas'],
            ['name' => 'Gestión Reclamos', 'description' => 'Registro y seguimiento'],
            ['name' => 'Actualización CRM', 'description' => 'Registro de interacciones'],
            ['name' => 'Reporte Incidencias', 'description' => 'Documentación de fallas'],
            ['name' => 'Coordinación Transporte', 'description' => 'Asignación de vehículos'],
            ['name' => 'Facturación Electrónica', 'description' => 'Emisión de documentos'],
            ['name' => 'Encuestas Satisfacción', 'description' => 'Evaluación post-entrega'],
            ['name' => 'Gestión Devoluciones', 'description' => 'Proceso RMA'],
            
            ['name' => 'Benchmarking', 'description' => 'Análisis competitivo'],
            ['name' => 'Prototipado', 'description' => 'Pruebas de concepto'],
            ['name' => 'Automatización RPA', 'description' => 'Implementación de bots'],
            ['name' => 'Optimización Flujos', 'description' => 'Rediseño de procesos'],
            ['name' => 'Pilotos Tecnológicos', 'description' => 'Pruebas nuevas tecnologías'],
            ['name' => 'Análisis Big Data', 'description' => 'Minería de datos'],
            ['name' => 'Gestión Cambio', 'description' => 'Implementación soluciones'],
            ['name' => 'Capacitación Tecnológica', 'description' => 'Entrenamiento equipos'],
            ['name' => 'Propuestas Innovación', 'description' => 'Documentación proyectos'],
            ['name' => 'Seguimiento KPIs', 'description' => 'Medición de impacto'],
            
            ['name' => 'Planificación Turnos', 'description' => 'Asignación de personal'],
            ['name' => 'Monitoreo SLA', 'description' => 'Cumplimiento de métricas'],
            ['name' => 'Capacitación Equipos', 'description' => 'Entrenamiento técnico'],
            ['name' => 'Reportes Gerenciales', 'description' => 'Resumen operativo'],
            ['name' => 'Auditorías Procesos', 'description' => 'Verificación estándares'],
            ['name' => 'Gestión Proveedores', 'description' => 'Relación con transportistas'],
            ['name' => 'Mejora Continua', 'description' => 'Proyectos Kaizen'],
            ['name' => 'Presupuestos', 'description' => 'Control operativo'],
            ['name' => 'Seguimiento Flotas', 'description' => 'Monitoreo GPS'],
            ['name' => 'Gestión Almacenes', 'description' => 'Control de inventarios'],
            
            ['name' => 'Inspección Física', 'description' => 'Verificación de carga'],
            ['name' => 'Muestreo Calidad', 'description' => 'Auditorías aleatorias'],
            ['name' => 'Validación SAP', 'description' => 'Conciliación en sistemas'],
            ['name' => 'Reporte NC', 'description' => 'No conformidades'],
            ['name' => 'Seguimiento Acciones', 'description' => 'Cierre de hallazgos'],
            ['name' => 'Checklist Pre-Embarque', 'description' => 'Listas de verificación'],
            ['name' => 'Control Documental', 'description' => 'Facturas y guías'],
            ['name' => 'Capacitación Equipos', 'description' => 'Entrenamiento estándares'],
            ['name' => 'Análisis Tendencia', 'description' => 'Reportes recurrentes'],
            ['name' => 'Coordinación Aduanas', 'description' => 'Requisitos legales'],
            
            ['name' => 'Optimización Rutas', 'description' => 'Algoritmos de eficiencia'],
            ['name' => 'Monitoreo GPS', 'description' => 'Seguimiento de flotas'],
            ['name' => 'Análisis Tarifario', 'description' => 'Comparativa de costos'],
            ['name' => 'Pronóstico Demanda', 'description' => 'Modelos predictivos'],
            ['name' => 'Scorecard Transportistas', 'description' => 'Evaluación proveedores'],
            ['name' => 'Simulación Escenarios', 'description' => 'Análisis what-if'],
            ['name' => 'Integración TMS', 'description' => 'Conexión de sistemas'],
            ['name' => 'Reportes Desempeño', 'description' => 'Indicadores clave'],
            ['name' => 'Gestión Combustible', 'description' => 'Optimización consumo'],
            ['name' => 'Planificación Estacional', 'description' => 'Previsión temporal']
        ];

        DB::table('organigram_skills')->insert($skills);
        DB::table('organigram_activities')->insert($activities);
    }
}