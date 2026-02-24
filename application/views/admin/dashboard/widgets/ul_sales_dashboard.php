<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-urbanladder-sales" data-name="Urban Ladder Sales Performance">
    <div class="row">
        <!-- Dashboard Header -->
        <div class="col-md-12 mbot15">
            <div class="tw-flex tw-items-center tw-justify-between">
                <h3 class="tw-font-bold tw-text-xl tw-text-neutral-800"><i class="fa fa-chart-line tw-text-blue-600"></i> Sales Intelligence Dashboard</h3>
                <span class="text-muted text-sm">Real-time Interior Design Analytics</span>
            </div>
        </div>

        <!-- Row 1: Source & Property Distribution -->
        <div class="col-md-6">
            <div class="panel_s tw-border-none tw-shadow-sm">
                <div class="panel-body">
                    <div class="widget-dragger"></div>
                    <div class="tw-flex tw-items-center tw-mb-4">
                        <div class="tw-p-2 tw-bg-blue-50 tw-rounded-lg tw-mr-3">
                            <i class="fa fa-bullhorn tw-text-blue-600"></i>
                        </div>
                        <h4 class="tw-font-semibold tw-text-neutral-700 tw-m-0">Leads by Source</h4>
                    </div>
                    <div class="relative" style="height:280px">
                        <canvas id="ul_source_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel_s tw-border-none tw-shadow-sm">
                <div class="panel-body">
                    <div class="widget-dragger"></div>
                    <div class="tw-flex tw-items-center tw-mb-4">
                        <div class="tw-p-2 tw-bg-amber-50 tw-rounded-lg tw-mr-3">
                            <i class="fa fa-home tw-text-amber-600"></i>
                        </div>
                        <h4 class="tw-font-semibold tw-text-neutral-700 tw-m-0">Property Distribution</h4>
                    </div>
                    <div class="relative" style="height:280px">
                        <canvas id="ul_property_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Agent Performance -->
        <div class="col-md-8">
            <div class="panel_s tw-border-none tw-shadow-sm">
                <div class="panel-body">
                    <div class="widget-dragger"></div>
                    <div class="tw-flex tw-items-center tw-mb-4">
                        <div class="tw-p-2 tw-bg-green-50 tw-rounded-lg tw-mr-3">
                            <i class="fa fa-users tw-text-green-600"></i>
                        </div>
                        <h4 class="tw-font-semibold tw-text-neutral-700 tw-m-0">Agent Booking Performance</h4>
                    </div>
                    <div class="relative" style="height:350px">
                        <canvas id="ul_agent_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Budget Analysis -->
        <div class="col-md-4">
            <div class="panel_s tw-border-none tw-shadow-sm">
                <div class="panel-body">
                    <div class="widget-dragger"></div>
                    <div class="tw-flex tw-items-center tw-mb-4">
                        <div class="tw-p-2 tw-bg-indigo-50 tw-rounded-lg tw-mr-3">
                            <i class="fa fa-wallet tw-text-indigo-600"></i>
                        </div>
                        <h4 class="tw-font-semibold tw-text-neutral-700 tw-m-0">Budget Breakdown</h4>
                    </div>
                    <div class="relative" style="height:350px">
                        <canvas id="ul_budget_chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const commonOptions = {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
            }
        };

        // Source Distribution (Pie)
        new Chart(document.getElementById('ul_source_chart'), {
            type: 'pie',
            data: <?php echo $ul_leads_by_source; ?>,
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                }
            }
        });

        // Property Type (Doughnut)
        new Chart(document.getElementById('ul_property_chart'), {
            type: 'doughnut',
            data: <?php echo $ul_property_type_stats; ?>,
            options: {
                ...commonOptions,
                cutout: '70%',
                plugins: {
                    ...commonOptions.plugins,
                }
            }
        });

        // Agent Chart (Bar)
        new Chart(document.getElementById('ul_agent_chart'), {
            type: 'bar',
            data: <?php echo $ul_agent_performance; ?>,
            options: {
                ...commonOptions,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { display: false } 
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Budget Analysis (Polar Area)
        new Chart(document.getElementById('ul_budget_chart'), {
            type: 'polarArea',
            data: <?php echo $ul_budget_stats; ?>,
            options: {
                ...commonOptions,
                scales: {
                    r: { ticks: { display: false } }
                },
                plugins: {
                    ...commonOptions.plugins,
                    legend: { display: false }
                }
            }
        });
    });
</script>
