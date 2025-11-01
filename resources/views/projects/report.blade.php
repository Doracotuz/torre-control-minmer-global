<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dossier de Proyecto: {{ $project->name }}</title>
    <style>
        @page { margin: 1.5cm; }
        :root {
            --color-primary: #2c3856;
            --color-accent: #ff9c00;
            --color-secondary: #4a5568;
            --color-border-light: #e2e8f0;
            --color-border-dark: #cbd5e0;
            --color-bg-light: #f7fafc;
            --color-success: #28a745;
            --color-warning: #dd6b20;
            --color-danger: #e53e3e;
            --color-inprogress: #3182ce;
            --color-pending: #ecc94b;
        }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: var(--color-secondary); line-height: 1.4; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: -1000; opacity: 0.04; width: 60%; background-image: url("{{ $logoBase64 ?? '' }}"); background-repeat: no-repeat; background-position: center; background-size: contain; height: 400px; }
        #footer { position: fixed; bottom: -1.5cm; left: -1.5cm; right: -1.5cm; height: 1.2cm; padding: 5px 1.5cm; background-color: white; z-index: 100; }
        #footer .footer-line { height: 3px; background: linear-gradient(to right, var(--color-primary), var(--color-accent)); }
        #footer table { width: 100%; font-size: 8px; color: var(--color-secondary); margin-top: 5px; }
        #header { border-bottom: 2px solid var(--color-border-light); padding-bottom: 12px; }
        #header table { width: 100%; }
        #header .logo { width: 150px; }
        #header .doc-info { text-align: right; vertical-align: top; }
        .doc-title { font-size: 18px; font-weight: bold; color: var(--color-primary); margin: 0; text-transform: uppercase; }
        .doc-subtitle { font-size: 11px; margin: 4px 0; color: var(--color-secondary); }
        .page-break { page-break-before: always; }

        .section-title {
            font-size: 14px; color: var(--color-primary); font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.8px; border-bottom: 2px solid var(--color-accent);
            padding-bottom: 6px; margin: 20px 0 10px 0; page-break-after: avoid;
        }
        .section-title.first { margin-top: 0; }
        .subsection-title {
             font-size: 11px; color: var(--color-primary); font-weight: bold;
             margin: 15px 0 5px 0; border-bottom: 1px solid var(--color-border-dark); padding-bottom: 3px;
             page-break-after: avoid;
        }

        .diag-box {
            padding: 12px 15px; border-radius: 5px; font-size: 10px; font-weight: bold;
            line-height: 1.5; margin: 15px 0; border: 1px solid; page-break-inside: avoid;
        }
        .diag-box strong { text-transform: uppercase; }
        .diag-box.success { background-color: #f0fff4; color: #2f855a; border-color: #9ae6b4; }
        .diag-box.warning { background-color: #fffaf0; color: #c05621; border-color: #fbd38d; }
        .diag-box.danger { background-color: #fff5f5; color: #c53030; border-color: #feb2b2; }

        .kpi-card {
            background-color: var(--color-bg-light); border: 1px solid var(--color-border-light);
            border-radius: 8px; padding: 15px; vertical-align: top; box-sizing: border-box;
        }
        .kpi-title { font-size: 10px; text-transform: uppercase; font-weight: bold; color: var(--color-primary); }
        .kpi-main { font-size: 24px; font-weight: bold; margin: 8px 0; }
        .kpi-main .unit { font-size: 16px; font-weight: normal; color: var(--color-secondary); }
        .kpi-main.on-track { color: var(--color-success); }
        .kpi-main.at-risk { color: var(--color-warning); }
        .kpi-main.overdue { color: var(--color-danger); }
        .kpi-subtext { font-size: 9px; color: var(--color-secondary); }

        .dual-chart-container { width: 100%; margin-top: 10px; }
        .dual-bar { margin-bottom: 8px; }
        .dual-bar-label { font-size: 9px; font-weight: bold; margin-bottom: 3px; }
        .dual-bar-bg { width: 100%; height: 18px; background-color: var(--color-border-light); border-radius: 9px; }
        .dual-bar-fill {
            height: 100%; border-radius: 9px;
            color: white; font-size: 10px; font-weight: bold;
            line-height: 18px; text-align: right; padding-right: 5px;
            box-sizing: border-box;
        }
        
        @php
            $total = $kpis['tasksTotal'] == 0 ? 1 : $kpis['tasksTotal'];
            $p_comp = round(($kpis['tasksCompleted'] / $total) * 100);
            $p_prog = round(($kpis['tasksInProgress'] / $total) * 100);
            $p_pend = 100 - $p_comp - $p_prog;
        @endphp
        .task-bar-container {
            width: 100%; height: 20px;
            background-color: var(--color-border-dark);
            border-radius: 5px;
            overflow: hidden;
            display: block;
            font-size: 0;
            margin-top: 10px;
        }
        .task-bar-segment {
            display: inline-block;
            height: 100%;
            font-size: 10px;
            color: white;
            font-weight: bold;
            line-height: 20px;
            text-align: center;
        }
        .task-bar-legend { font-size: 9px; margin-top: 8px; }
        .legend-item { margin-right: 10px; display: inline-block; }
        .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 4px; vertical-align: middle; }

        
        .workload-chart { width: 100%; border-spacing: 0 10px; }
        .workload-label { width: 30%; font-weight: bold; padding-right: 10px; vertical-align: middle; }
        .workload-bar-cell { width: 70%; vertical-align: middle; }
        .workload-bar-bg {
            background-color: var(--color-border-light);
            border-radius: 5px; height: 16px;
            font-size: 0;
        }
        .workload-bar-fill {
            display: inline-block; height: 100%;
            font-size: 9px; color: white; line-height: 16px;
            text-align: center; font-weight: bold;
        }
        .workload-ontrack { background-color: var(--color-success); border-radius: 5px 0 0 5px; }
        .workload-overdue { background-color: var(--color-warning); border-radius: 0 5px 5px 0; }
        .workload-ontrack.only { border-radius: 5px; }
        .workload-overdue.only { border-radius: 5px; }


        .styled-table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: auto; }
        .styled-table th, .styled-table td { padding: 8px; border-bottom: 1px solid var(--color-border-light); text-align: left; }
        .styled-table th { background-color: var(--color-bg-light); font-weight: bold; color: var(--color-primary); font-size: 9px; text-transform: uppercase; }
        .styled-table tr { page-break-inside: avoid; } /* Evita que las filas se corten */
        .styled-table .task-overdue { color: var(--color-danger); font-weight: bold; }
        .styled-table .task-inprogress { color: var(--color-inprogress); font-weight: bold; }
        .styled-table .task-pending { color: var(--color-warning); font-weight: bold; }
        .styled-table .task-completada { color: var(--color-secondary); text-decoration: line-through; }
        .styled-table .total-row { background-color: var(--color-bg-light); border-top: 2px solid var(--color-border-dark); font-weight: bold; }
        .styled-table .total-row td { color: var(--color-primary); }
        .styled-table .priority-Alta { color: var(--color-danger); font-weight: bold; }
        .styled-table .priority-Media { color: var(--color-warning); }
        
        .comment-bubble { background-color: var(--color-bg-light); border: 1px solid var(--color-border-light); padding: 8px 12px; border-radius: 8px; margin-bottom: 8px; page-break-inside: avoid; }
        .comment-header { font-size: 8px; font-weight: bold; margin-bottom: 4px; }
        .comment-user { color: var(--color-primary); }
        .comment-body { font-size: 10px; font-style: italic; }

    </style>
</head>
<body>
    @php use Carbon\Carbon; Carbon::setLocale('es'); @endphp
    <div class="watermark"></div>

    <footer id="footer">
        <div class="footer-line"></div>
        <table>
            <tr>
                <td>Dossier de Proyecto: {{ $project->name }} | Documento Confidencial</td>
                <td style="text-align: right;" class="page-number"></td>
            </tr>
        </table>
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Página {PAGE_NUM} de {PAGE_COUNT}"; $size = 8; $font = $fontMetrics->getFont("DejaVu Sans");
                $width = $fontMetrics->get_text_width($text, $font, $size); $x = $pdf->get_width() - $width - 40;
                $y = $pdf->get_height() - 34; $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>
    </footer>

    <header id="header">
        <table>
            <tr>
                <td style="width: 50%;">
                    @if(isset($logoBase64)) <img src="{{ $logoBase64 }}" alt="Logo" class="logo"> @endif
                </td>
                <td style="width: 50%;" class="doc-info">
                    <h1 class="doc-title">Dossier de Proyecto</h1>
                    <p class="doc-subtitle">{{ $project->name }}</p>
                    <p class="doc-subtitle">Generado: {{ \Carbon\Carbon::now()->isoFormat('D MMMM, YYYY') }}</p>
                </td>
            </tr>
        </table>
    </header>

    <main>
        
        <h2 class="section-title first">Dashboard Ejecutivo</h2>
        
        <div class="diag-box {{ $diagnosis['level'] }}">
            <strong>Diagnóstico Ejecutivo:</strong> {{ $diagnosis['message'] }}
        </div>

        <table style="width: 100%; border-spacing: 15px 0; margin-left: -15px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    
                    <div class="kpi-card">
                        <div class="kpi-title">Salud del Calendario</div>
                        @php $dias = $kpis['daysRemaining']; $status = $kpis['healthStatus']; @endphp
                        @if(is_null($dias))
                            <div class="kpi-main on-track">N/D</div>
                            <div class="kpi-subtext">No hay fecha de entrega definida.</div>
                        @else
                            <div class="kpi-main {{ $status == 'overdue' ? 'overdue' : ($status == 'at-risk' ? 'at-risk' : 'on-track') }}">
                                {{-- FIX: Formato de número sin decimales --}}
                                {{ number_format(abs($dias), 0) }} 
                                <span class="unit">{{ $status == 'overdue' ? 'Días Vencido' : 'Días' }}</span>
                            </div>
                            <div class="kpi-subtext">
                                @if($status == 'overdue') Venció el: {{ $project->due_date->format('d/m/Y') }}
                                @else Restantes para: {{ $project->due_date->format('d/m/Y') }} @endif
                            </div>
                        @endif
                    </div>

                    <div class="kpi-card" style="margin-top: 15px;">
                        <div class="kpi-title">Salud Financiera</div>
                        <div class="kpi-main">{{ number_format($kpis['budgetProgress'], 0) }}<span class="unit">% Usado</span></div>
                        @php $budgetAlert = $kpis['budgetProgress'] > 100 ? 'danger' : ($kpis['budgetProgress'] > 90 ? 'warning' : 'success'); @endphp
                        <div class="dual-bar-bg">
                            <div class="dual-bar-fill {{ $budgetAlert }}" style="width: {{ $kpis['budgetProgress'] > 100 ? 100 : $kpis['budgetProgress'] }}%;"></div>
                        </div>
                        <div class="kpi-subtext">
                            ${{ number_format($kpis['totalSpent'], 2) }} / ${{ number_format($kpis['budget'], 2) }}
                        </div>
                    </div>

                    <div class="kpi-card" style="margin-top: 15px;">
                        <div class="kpi-title">Análisis de Eficiencia</div>
                        <div class="dual-chart-container" style="margin-top: 15px;">
                            <div class="dual-bar">
                                <div class="dual-bar-label">Avance del Proyecto (Alcance)</div>
                                <div class="dual-bar-bg">
                                    <div class="dual-bar-fill" style="width: {{ $kpis['progress'] }}%; background-color: var(--color-primary);">
                                        {{ number_format($kpis['progress'], 0) }}%
                                    </div>
                                </div>
                            </div>
                            <div class="dual-bar">
                                <div class="dual-bar-label">Consumo de Presupuesto (Costo)</div>
                                <div class="dual-bar-bg">
                                    <div class="dual-bar-fill {{ $budgetAlert }}" style="width: {{ $kpis['budgetProgress'] > 100 ? 100 : $kpis['budgetProgress'] }}%;">
                                        {{ number_format($kpis['budgetProgress'], 0) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                
                <td style="width: 50%; vertical-align: top;">
                    
                    <div class="kpi-card">
                        <div class="kpi-title">Avance de Tareas ({{ $kpis['tasksTotal'] }} Totales)</div>
                        <div class="task-bar-container">
                            <div class="task-bar-segment" style="width: {{ $p_comp }}%; background-color: var(--color-success);">{{ $p_comp > 10 ? $p_comp.'%' : '' }}</div>
                            <div class="task-bar-segment" style="width: {{ $p_prog }}%; background-color: var(--color-inprogress);">{{ $p_prog > 10 ? $p_prog.'%' : '' }}</div>
                            <div class="task-bar-segment" style="width: {{ $p_pend }}%; background-color: var(--color-pending);">{{ $p_pend > 10 ? $p_pend.'%' : '' }}</div>
                        </div>
                        <div class="task-bar-legend">
                            <span class="legend-item"><span class="dot" style="background-color: var(--color-success);"></span> {{ $kpis['tasksCompleted'] }} Completadas</span>
                            <span class="legend-item"><span class="dot" style="background-color: var(--color-inprogress);"></span> {{ $kpis['tasksInProgress'] }} En Progreso</span>
                            <span class="legend-item"><span class="dot" style="background-color: var(--color-pending);"></span> {{ $kpis['tasksPending'] }} Pendientes</span>
                        </div>
                    </div>
                    
                    <div class="kpi-card" style="margin-top: 15px;">
                        <div class="kpi-title">Carga de Trabajo del Equipo (Tareas Activas)</div>
                        <table class="workload-chart" style="margin-top: 10px;">
                            @php $max_tasks = max(1, $teamWorkload->max('total_active')); @endphp
                            @forelse($teamWorkload as $workload)
                            <tr>
                                <td class="workload-label">{{ $workload['name'] }}</td>
                                <td class="workload-bar-cell">
                                    <div class="workload-bar-bg">
                                        @php
                                            $total = max(1, $workload['total_active']);
                                            $onTrackWidth = ($workload['on_track'] / $total) * 100;
                                            $overdueWidth = ($workload['overdue'] / $total) * 100;
                                        @endphp
                                        <div class="workload-bar-fill workload-ontrack {{ $overdueWidth == 0 ? 'only' : '' }}" style="width: {{ $onTrackWidth }}%;">
                                            {{ $workload['on_track'] > 0 ? $workload['on_track'] : '' }}
                                        </div>
                                        <div class="workload-bar-fill workload-overdue {{ $onTrackWidth == 0 ? 'only' : '' }}" style="width: {{ $overdueWidth }}%;">
                                            {{ $workload['overdue'] > 0 ? $workload['overdue'] : '' }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td style="text-align: center; padding: 10px;">N/A</td></tr>
                            @endforelse
                        </table>
                        <div class="task-bar-legend" style="margin-top: 10px;">
                            <span class="legend-item"><span class="dot" style="background-color: var(--color-success);"></span> A Tiempo</span>
                            <span class="legend-item"><span class="dot" style="background-color: var(--color-warning);"></span> Vencidas</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        

        <h2 class="section-title">Apéndices de Auditoría</h2>

        <div class="subsection-title">Apéndice A: Desglose de Gastos</div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Registrado por</th>
                    <th style="text-align: right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->expenses as $expense)
                <tr>
                    <td style="width: 15%;">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                    <td>{{ $expense->description }}</td>
                    <td style="width: 20%;">{{ $expense->user->name ?? 'N/A' }}</td>
                    <td style="text-align: right; width: 15%;">${{ number_format($expense->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align: center;">No hay gastos registrados.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">GASTO TOTAL:</td>
                    <td style="text-align: right;">${{ number_format($kpis['totalSpent'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="subsection-title">Apéndice B: Detalle de Tareas</div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Tarea</th>
                    <th>Asignado a</th>
                    <th>Prioridad</th>
                    <th>Vencimiento</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allTasks as $task)
                <tr class="task-{{ strtolower($task->status) }}">
                    <td style="width: 40%;">{{ $task->name }}</td>
                    <td style="width: 20%;">{{ $task->assignee->name ?? 'Sin asignar' }}</td>
                    <td style="width: 10%;" class="priority-{{ $task->priority }}">{{ $task->priority }}</td>
                    <td style="width: 15%;">{{ $task->due_date ? $task->due_date->format('d/m/Y') : 'N/D' }}</td>
                    <td style="width: 15%; font-weight: bold;">{{ $task->status }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center;">No hay tareas en este proyecto.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="subsection-title">Apéndice C: Bitácora de Comentarios</div>
        <div class="comment-log">
            @forelse($comments as $comment)
            <div class="comment-bubble">
                <div class="comment-header">
                    <span class="comment-user">{{ $comment->user->name ?? 'Usuario' }}</span>
                    | {{ $comment->created_at->isoFormat('D MMMM, YYYY - h:mm a') }}
                </div>
                <div class="comment-body">{!! nl2br(e($comment->body)) !!}</div>
            </div>
            @empty
            <div style="text-align: center; font-size: 10px; padding: 10px;">No hay comentarios registrados.</div>
            @endforelse
        </div>
        
        <div class="subsection-title">Apéndice D: Registro de Archivos</div>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Nombre del Archivo</th>
                    <th>Subido por</th>
                    <th>Fecha</th>
                    <th style="text-align: right;">Tamaño</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->files as $file)
                <tr>
                    <td style="width: 45%;">{{ Str::limit($file->file_name, 50) }}</td>
                    <td style="width: 25%;">{{ $file->user->name ?? 'N/A' }}</td>
                    <td style="width: 15%;">{{ $file->created_at->format('d/m/Y') }}</td>
                    <td style="text-align: right; width: 15%;">{{ number_format($file->file_size / 1048576, 2) }} MB</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align: center;">No hay archivos adjuntos.</td></tr>
                @endforelse
            </tbody>
        </table>

    </main>
</body>
</html>