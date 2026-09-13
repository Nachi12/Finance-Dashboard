<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Dashboard - Family Financial Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* KPI Refinements */
        .primary-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 15px; }
        .secondary-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .primary-kpi-card { background: white; padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: center; border-top: 4px solid var(--border-color); }
        .secondary-kpi-card { background: var(--light-bg); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color); }
        .kpi-trend { font-size: 0.85em; margin-top: 8px; font-weight: 500; }
        .kpi-trend.positive { color: var(--success-color); }
        .kpi-trend.negative { color: var(--danger-color); }
        .kpi-trend.neutral { color: var(--text-muted); }
        .summary-sentence { font-size: 1.1rem; color: var(--text-color); margin-bottom: 20px; padding: 15px 20px; background: #f8fbff; border-left: 4px solid var(--primary-color); border-radius: 8px; border: 1px solid #e1efff; line-height: 1.5; }

        /* Progressive Disclosure Accordions */
        .fin-accordion {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        .fin-accordion summary {
            padding: 18px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            cursor: pointer;
            list-style: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
            border-bottom: 1px solid transparent;
            transition: background 0.2s;
        }
        .fin-accordion summary:hover { background: #f0f4f8; }
        .fin-accordion summary::-webkit-details-marker { display: none; }
        .fin-accordion summary::after {
            content: '▼';
            font-size: 0.8em;
            color: var(--text-muted);
            transition: transform 0.2s ease;
        }
        .fin-accordion[open] summary { border-bottom-color: var(--border-color); background: white; }
        .fin-accordion[open] summary::after { transform: rotate(180deg); }
        .fin-accordion-content { padding: 20px; }
        
        /* Clean up legacy sections to match new flat look */
        .dashboard-section { box-shadow: none !important; border: 1px solid var(--border-color); background: white; }
        .dashboard-header-block { box-shadow: none !important; border: 1px solid var(--border-color); }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="desktop-sidebar-title">Finance Dashboard</h2>
            <h2 class="mobile-sidebar-title" style="display: none;">Menu</h2>
            <button class="sidebar-close-btn" id="sidebarCloseBtn" style="display: none; background: none; border: none; color: white; font-size: 2rem; cursor: pointer;">&times;</button>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php" class="active"><span class="nav-icon">🏠</span> <span class="nav-label">Dashboard</span></a></li>
                <li><a href="#" data-modal-target="addIncomeModal"><span class="nav-icon">💰</span> <span class="nav-label">Income</span></a></li>
                <li><a href="#" data-modal-target="addExpenseModal"><span class="nav-icon">💸</span> <span class="nav-label">Expenses</span></a></li>
                <li><a href="#" data-modal-target="addLoanModal"><span class="nav-icon">💳</span> <span class="nav-label">Loans & EMI</span></a></li>
                <li><a href="#" data-modal-target="addAccountModal"><span class="nav-icon">🏦</span> <span class="nav-label">Bank Accounts</span></a></li>
                <li><a href="#" data-modal-target="addInvestmentModal"><span class="nav-icon">📈</span> <span class="nav-label">Investments</span></a></li>
                <li><a href="#" data-modal-target="addAssetModal"><span class="nav-icon">🏠</span> <span class="nav-label">Assets</span></a></li>
                <li><a href="#" data-modal-target="addBudgetModal"><span class="nav-icon">🎯</span> <span class="nav-label">Budget</span></a></li>
                <li><a href="#" data-modal-target="addGoalModal"><span class="nav-icon">🎯</span> <span class="nav-label">Goals</span></a></li>
                <li><a href="#"><span class="nav-icon">📊</span> <span class="nav-label">Reports</span></a></li>
                <li class="mobile-only" style="display: none;"><a href="#"><span class="nav-icon">👨‍👩‍👧</span> <span class="nav-label">Family</span></a></li>
                <li class="mobile-only" style="display: none;"><a href="#"><span class="nav-icon">⚙️</span> <span class="nav-label">Settings</span></a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
                        <header class="topbar responsive-topbar">
            <div class="topbar-left">
                <button class="mobile-menu-btn" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--primary-color);">☰</button>
                <span class="mobile-title" style="font-weight: bold; font-size: 1.2rem; display: none;">Family Finance</span>
            </div>
            <div class="search-box">
                <input type="text" placeholder="Search..." class="form-control search-input" style="width: 250px;">
            </div>
            <div class="topbar-right">
                <span class="desktop-month">Month: Sep 2026</span>
                <span>Profile</span>
            </div>
        </header>

        <!-- Dashboard Body -->
        <div class="dashboard-body">
            <!-- Page Header & Filters -->
            <div class="dashboard-header-block" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 20px;">
                <div class="header-titles">
                    <h1 style="margin: 0 0 5px 0; font-size: 1.5rem; color: var(--primary-color);">Family Financial Dashboard</h1>
                    <p class="text-muted" style="margin: 0; font-size: 0.9rem;">Currently Viewing: <strong id="viewing-label">All Family (Sep 2026)</strong></p>
                </div>
                
                <div class="dashboard-filters" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div>
                        <select id="filter-member" class="form-control" style="min-width: 0; padding: 8px;">
                            <option value="ALL">All Family</option>
                        </select>
                    </div>
                    <div>
                        <select id="filter-month" class="form-control" style="padding: 8px;">
                            <option value="01">Jan</option><option value="02">Feb</option><option value="03">Mar</option>
                            <option value="04">Apr</option><option value="05">May</option><option value="06">Jun</option>
                            <option value="07">Jul</option><option value="08">Aug</option><option value="09" selected>Sep</option>
                            <option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option>
                        </select>
                    </div>
                    <div>
                        <select id="filter-year" class="form-control" style="padding: 8px;">
                            <option value="2025">2025</option><option value="2026" selected>2026</option><option value="2027">2027</option>
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-primary" id="btn-apply-filters" style="padding: 8px 15px;">Apply</button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions" style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; background: white; padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                <button class="btn" style="flex: 1; min-width: 0; background: rgba(150, 200, 150, 0.15); color: var(--success-color); border: 1px solid var(--success-color);" data-modal-target="addIncomeModal">⬇️ Add Income</button>
                <button class="btn" style="flex: 1; min-width: 0; background: rgba(250, 150, 150, 0.15); color: var(--danger-color); border: 1px solid var(--danger-color);" data-modal-target="addExpenseModal">⬆️ Add Expense</button>
                <button class="btn" style="flex: 1; min-width: 0; background: rgba(250, 200, 100, 0.15); color: var(--warning-color); border: 1px solid var(--warning-color);" data-modal-target="addLoanModal">📝 Add Loan</button>
                <button class="btn" style="flex: 1; min-width: 0; background: var(--primary-color); color: white;" data-modal-target="addEmiModal">✓ Mark EMI Paid</button>
            </div>

            <!-- KPI Grid (Dynamic) -->
            <!-- Financial Summary Sentence -->
            <div id="dynamic-summary-sentence" class="summary-sentence">
                Loading financial position...
            </div>

            <!-- Primary Metrics (Current Month Cash Flow) -->
            <div class="primary-kpi-grid">
                <div class="primary-kpi-card" style="border-top-color: var(--primary-color);">
                    <div class="kpi-card-title">Family Income</div>
                    <div class="kpi-card-value" id="kpi-income" style="font-size: 1.8rem; font-weight: bold;">Loading...</div>
                    <div class="kpi-trend neutral" id="trend-income">Calculating...</div>
                </div>
                <div class="primary-kpi-card" style="border-top-color: var(--danger-color);">
                    <div class="kpi-card-title">Family Expenses</div>
                    <div class="kpi-card-value" id="kpi-expenses" style="font-size: 1.8rem; font-weight: bold;">Loading...</div>
                    <div class="kpi-trend neutral" id="trend-expenses">Calculating...</div>
                </div>
                <div class="primary-kpi-card" style="border-top-color: var(--warning-color);">
                    <div class="kpi-card-title">Total EMI</div>
                    <div class="kpi-card-value" id="kpi-emi" style="font-size: 1.8rem; font-weight: bold;">Loading...</div>
                    <div class="kpi-trend neutral" id="trend-emi">Calculating...</div>
                </div>
                <div class="primary-kpi-card" style="border-top-color: var(--success-color);">
                    <div class="kpi-card-title">Available Cash Flow</div>
                    <div class="kpi-card-value" id="kpi-savings" style="font-size: 1.8rem; font-weight: bold;">Loading...</div>
                    <div class="kpi-trend neutral" id="trend-savings">Calculating...</div>
                </div>
            </div>

            <!-- Secondary Metrics (Wealth & Debt) -->
            <div class="secondary-kpi-grid">
                <div class="secondary-kpi-card">
                    <div class="kpi-card-title" style="font-size: 0.85em;">Outstanding Debt</div>
                    <div class="kpi-card-value" id="kpi-debt" style="font-size: 1.3rem; color: var(--danger-color); margin-top: 5px;">Loading...</div>
                    <div class="kpi-trend neutral" id="trend-debt">Calculating...</div>
                </div>
                <div class="secondary-kpi-card">
                    <div class="kpi-card-title" style="font-size: 0.85em;">Total Investments</div>
                    <div class="kpi-card-value" id="kpi-investments" style="font-size: 1.3rem; color: var(--primary-color); margin-top: 5px;">Loading...</div>
                    <div class="kpi-trend neutral" id="trend-investments">Calculating...</div>
                </div>
                <div class="secondary-kpi-card">
                    <div class="kpi-card-title" style="font-size: 0.85em;">Net Worth</div>
                    <div class="kpi-card-value" id="kpi-net-worth" style="font-size: 1.3rem; font-weight: bold; margin-top: 5px;">Loading...</div>
                    <div class="kpi-trend neutral" id="trend-net-worth">Calculating...</div>
                </div>
                <div class="secondary-kpi-card">
                    <div class="kpi-card-title" style="font-size: 0.85em;">Savings Rate</div>
                    <div class="kpi-card-value" id="kpi-savings-rate" style="font-size: 1.3rem; font-weight: bold; color: var(--success-color); margin-top: 5px;">-</div>
                    <div class="kpi-trend neutral" id="trend-savings-rate">Calculating...</div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="dashboard-section responsive-grid" style="display: grid; gap: 20px; margin-bottom: 25px;">
                <div class="chart-container" style="background: white; padding: 20px; border-radius: 8px;">
                    <div class="section-header">
                        <h3>Income vs Expenses (This Month)</h3>
                    </div>
                    <canvas id="cashflowChart"></canvas>
                </div>
                
                <div class="chart-container" style="background: white; padding: 20px; border-radius: 8px;">
                    <div class="section-header">
                        <h3>Expense Breakdown</h3>
                    </div>
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>

            <!-- Action Items / Upcoming EMIs -->
            <div class="dashboard-section" id="upcoming-emis-section" style="margin-bottom: 25px; display: none; border-color: var(--warning-color); border-width: 2px;">
                <h3 style="margin: 0; padding: 15px; color: var(--warning-color); display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border-color); background: #fffdf5;">
                    ⚠️ Action Required: Upcoming/Overdue EMIs
                </h3>
                <div id="upcoming-emis-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; padding: 15px;">
                    <!-- Dynamically populated via JS -->
                </div>
            </div>

            <!-- 6. Loan & EMI Dashboard Section -->
            <div class="dashboard-section loan-dashboard-section" style="margin-top: 20px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Loan & EMI Management</h3>
                    <div>
                        <button class="btn btn-primary" data-modal-target="addLoanModal">+ Add Loan</button>
                        <button class="btn btn-secondary" data-modal-target="addEmiModal">+ Record EMI</button>
                    </div>
                </div>
                
                <div class="kpi-grid" style="gap: 15px; margin-bottom: 20px;">
                    <div class="kpi-card" style="background: var(--light-bg); border-left: 4px solid var(--warning-color); padding: 15px; border-radius: 4px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 0.9em; color: var(--text-color);">Total Outstanding</h4>
                        <div class="value" id="loan-kpi-outstanding" style="font-size: 1.4em; font-weight: bold; color: var(--danger-color);">₹0</div>
                    </div>
                    <div class="kpi-card" style="background: var(--light-bg); border-left: 4px solid var(--danger-color); padding: 15px; border-radius: 4px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 0.9em; color: var(--text-color);">Total Monthly EMI</h4>
                        <div class="value" id="loan-kpi-monthly-emi" style="font-size: 1.4em; font-weight: bold; color: var(--warning-color);">₹0</div>
                    </div>
                    <div class="kpi-card" style="background: var(--light-bg); border-left: 4px solid var(--success-color); padding: 15px; border-radius: 4px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 0.9em; color: var(--text-color);">Principal Repaid</h4>
                        <div class="value" id="loan-kpi-principal-paid" style="font-size: 1.4em; font-weight: bold; color: var(--success-color);">₹0</div>
                    </div>
                    <div class="kpi-card" style="background: var(--light-bg); border-left: 4px solid var(--secondary-color); padding: 15px; border-radius: 4px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 0.9em; color: var(--text-color);">Interest Paid</h4>
                        <div class="value" id="loan-kpi-interest-paid" style="font-size: 1.4em; font-weight: bold; color: var(--text-color);">₹0</div>
                    </div>
                </div>

                <h4 style="margin-bottom: 10px;">Active Loans Overview</h4>
                <div style="margin-bottom: 20px; overflow-x: auto;">
                    <table class="table table-cards-mobile" style="width: 100%; border-collapse: collapse; font-size: 0.95em;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid var(--border-color); background: var(--light-bg);">
                                <th style="padding: 10px;">Loan</th>
                                <th style="padding: 10px;">Member</th>
                                <th style="padding: 10px;">Outstanding</th>
                                <th style="padding: 10px;">EMI</th>
                                <th style="padding: 10px;">Next Due</th>
                                <th style="padding: 10px; width: 120px;">Progress</th>
                                <th style="padding: 10px;">Status</th>
                                <th style="padding: 10px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="active-loans-table-body">
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 20px;" class="text-muted">Loading active loans...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 7. Recent Activity -->
            <details class="fin-accordion" open>
                <summary>Recent Activity</summary>
                <div class="fin-accordion-content">
                    <div style="overflow-x: auto;">
                        <table class="table table-cards-mobile" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 1px solid var(--border-color);">
                                    <th style="padding: 10px;">Date</th>
                                    <th style="padding: 10px;">Type</th>
                                    <th style="padding: 10px;">Description</th>
                                    <th style="padding: 10px;">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="recentActivityTableBody">
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px;" class="text-muted">Loading activity...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>

            <!-- 8. Budget & Spending Analysis -->
            <details class="fin-accordion">
                <summary>Budget & Spending Analysis</summary>
                <div class="fin-accordion-content text-muted" style="text-align: center; padding: 40px;">
                    Detailed budget tracking and category spending analysis will appear here.
                </div>
            </details>

            <!-- 9. Investments / Net Worth (includes Member Comparison) -->
            <details class="fin-accordion">
                <summary>Investments & Member Comparison</summary>
                <div class="fin-accordion-content member-comparison-section">
                    <h4 style="margin: 0 0 15px 0;">Member Comparison</h4>
                    <div style="overflow-x: auto;">
                        <table class="table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; border-bottom: 1px solid var(--border-color);">
                                    <th style="padding: 10px;">Member</th>
                                    <th style="padding: 10px;">Income</th>
                                    <th style="padding: 10px;">Expenses</th>
                                    <th style="padding: 10px;">EMI</th>
                                    <th style="padding: 10px;">Savings</th>
                                    <th style="padding: 10px;">Debt</th>
                                    <th style="padding: 10px;">Net Worth</th>
                                </tr>
                            </thead>
                            <tbody id="memberComparisonTableBody">
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px;" class="text-muted">Loading comparison...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>
            
            <!-- Become Debt-Free Section -->
            <details class="fin-accordion debt-free-section" style="border: 2px solid var(--primary-color);">
                <summary style="font-size: 1.2em; color: var(--primary-color);">🌟 Become Debt-Free Analyzer</summary>
                <div class="fin-accordion-content">
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="color: var(--primary-color); margin: 0;">Advisor Summary</h3>
                        <button class="btn btn-secondary" data-modal-target="settingsModal">⚙️ Analyzer Settings</button>
                    </div>
                
                    <!-- Primary Status & Freedom Date -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #e8f5e9; padding: 20px; border-radius: 8px; border-left: 4px solid var(--success-color);">
                        <div>
                            <div style="font-size: 0.9em; color: var(--success-color); font-weight: bold; text-transform: uppercase;">Debt-Free Status</div>
                            <div id="df-status-text" style="font-size: 1.6em; font-weight: bold; color: var(--text-color);">Analyzing...</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.9em; color: var(--text-muted);">Estimated Debt-Free Date</div>
                            <div id="df-freedom-date" style="font-size: 1.6em; font-weight: bold; color: var(--primary-color);">-</div>
                        </div>
                    </div>

                    <!-- Core KPIs -->
                    <div class="kpi-grid" style="gap: 15px; margin-bottom: 30px;">
                        <div class="kpi-card" style="background: var(--light-bg); border-left: 4px solid var(--danger-color); padding: 15px; border-radius: 4px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 0.9em;">Current Debt</h4>
                            <div class="value" id="df-current-debt" style="font-size: 1.4em; font-weight: bold;">₹0</div>
                        </div>
                        <div class="kpi-card" style="background: var(--light-bg); border-left: 4px solid var(--warning-color); padding: 15px; border-radius: 4px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 0.9em;">Monthly EMI</h4>
                            <div class="value" id="df-monthly-emi" style="font-size: 1.4em; font-weight: bold;">₹0</div>
                        </div>
                        <div class="kpi-card" style="background: var(--light-bg); border-left: 4px solid var(--success-color); padding: 15px; border-radius: 4px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 0.9em;">Extra Capacity</h4>
                            <div class="value" id="df-extra-capacity-main" style="font-size: 1.4em; font-weight: bold;">₹0</div>
                        </div>
                        <div class="kpi-card" style="background: var(--light-bg); border-left: 4px solid var(--primary-color); padding: 15px; border-radius: 4px;">
                            <h4 style="margin: 0 0 10px 0; font-size: 0.9em;">Potential Saving</h4>
                            <div class="value" id="df-potential-saving" style="font-size: 1.4em; font-weight: bold; color: var(--success-color);">₹0</div>
                        </div>
                    </div>

                    <!-- Recommended Strategy -->
                    <div style="background: var(--light-bg); padding: 20px; border-radius: 8px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 2.2em;">📈</div>
                        <div>
                            <h4 style="margin: 0 0 5px 0;">Recommended Strategy: <span id="df-strategy-label" style="color: var(--primary-color);">Avalanche</span></h4>
                            <p style="margin: 0; font-size: 0.95em; color: var(--text-muted);" id="df-strategy-explanation">
                                This strategy minimizes the total interest paid by targeting your highest interest rate debts first.
                            </p>
                        </div>
                    </div>

                    <!-- Recommendations Engine -->
                    <h4 style="margin-bottom: 15px;">Top Recommendations</h4>
                    <div id="recommendations-container" style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
                        <div style="padding: 15px; background: var(--light-bg); border-radius: 8px; color: var(--text-muted);">
                            Loading AI recommendations based on your data...
                        </div>
                    </div>

                    <!-- Advanced Analysis -->
                    <details class="fin-accordion">
                        <summary style="background: var(--light-bg); padding: 12px 15px; border-radius: 6px; font-weight: bold; cursor: pointer; list-style: none;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span>📊</span>
                                <span>View Detailed Projection</span>
                            </div>
                        </summary>
                        <div class="fin-accordion-content" style="padding-top: 20px;">
                            <div style="margin-bottom: 20px;">
                                <h5>Scenario Comparison</h5>
                                <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                                    <thead>
                                        <tr style="text-align: left; border-bottom: 1px solid var(--border-color);">
                                            <th style="padding: 10px;">Scenario</th>
                                            <th style="padding: 10px;">Extra / Mo</th>
                                            <th style="padding: 10px;">Months to Freedom</th>
                                            <th style="padding: 10px;">Total Interest</th>
                                            <th style="padding: 10px;">Interest Saved</th>
                                        </tr>
                                    </thead>
                                    <tbody id="df-scenariosBody">
                                        <tr><td colspan="5" style="text-align: center; padding: 10px;">Loading scenarios...</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div style="margin-top: 30px; overflow-x: auto;">
                                <h5>Monthly Repayment Roadmap</h5>
                                <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9em;">
                                    <thead>
                                        <tr style="text-align: left; border-bottom: 1px solid var(--border-color);">
                                            <th style="padding: 10px;">Mth</th>
                                            <th style="padding: 10px;">Target Loan</th>
                                            <th style="padding: 10px;">Opening Debt</th>
                                            <th style="padding: 10px;">Regular EMI</th>
                                            <th style="padding: 10px;">Extra Payment</th>
                                            <th style="padding: 10px;">Interest</th>
                                            <th style="padding: 10px;">Principal Repaid</th>
                                            <th style="padding: 10px;">Closing Debt</th>
                                        </tr>
                                    </thead>
                                    <tbody id="df-roadmapBody">
                                        <tr><td colspan="8" style="text-align: center; padding: 10px;">Loading roadmap...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                </div>
            </details>
        </div>
    </main>
</div>

<!-- Add Income Modal -->
<div class="modal-overlay" id="addIncomeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Income</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addIncomeForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Family Member</label>
                    <select name="family_member_id" class="form-control fm-select" required>
                        <option value="">Select Member</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Income Source</label>
                    <input type="text" name="income_source" class="form-control" placeholder="e.g. TechCorp Inc." required>
                </div>
                <div class="form-group">
                    <label>Income Type</label>
                    <select name="income_type" class="form-control" required>
                        <option value="Salary">Salary</option>
                        <option value="Business">Business</option>
                        <option value="Pension">Pension</option>
                        <option value="Freelance">Freelance</option>
                        <option value="Interest">Interest</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Recurring?</label>
                    <select name="recurring" class="form-control">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Income</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal-overlay" id="addExpenseModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Expense</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addExpenseForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Family Member</label>
                    <select name="family_member_id" class="form-control fm-select-expense" required>
                        <option value="">Select Member</option>
                        <option value="FAMILY_SHARED">Shared Family Expense</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control" required>
                        <option value="Rent">Rent</option>
                        <option value="Food">Food</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Education">Education</option>
                        <option value="Medical">Medical</option>
                        <option value="Transportation">Transportation</option>
                        <option value="EMI">EMI</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Shopping">Shopping</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Family">Family</option>
                        <option value="Business">Business</option>
                        <option value="Subscriptions">Subscriptions</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" placeholder="What was this for?">
                </div>
                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <input type="text" name="payment_method" class="form-control" placeholder="e.g. Credit Card">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Expense</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Loan Modal -->
<div class="modal-overlay" id="addLoanModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Add New Loan</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addLoanForm" novalidate>
            <div class="modal-body" style="padding: 20px;">
                <!-- Basic Details -->
                <div class="form-section">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">1. Basic Details</h4>
                    <div class="form-group">
                        <label>Family Member <span class="text-danger">*</span></label>
                        <select name="family_member_id" class="form-control fm-select" required>
                            <option value="">Select Member</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Loan Name <span class="text-danger">*</span></label>
                        <input type="text" name="loan_name" class="form-control" placeholder="e.g. Home Loan" required>
                    </div>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Lender (Bank Name) <span class="text-danger">*</span></label>
                            <input type="text" name="lender" class="form-control" placeholder="e.g. HDFC Bank" required>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Loan Type <span class="text-danger">*</span></label>
                            <select name="loan_type" class="form-control" required>
                                <option value="Personal Loan">Personal Loan</option>
                                <option value="Home Loan">Home Loan</option>
                                <option value="Vehicle Loan">Vehicle Loan</option>
                                <option value="Gold Loan">Gold Loan</option>
                                <option value="Education Loan">Education Loan</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Loan Amount & EMI -->
                <div class="form-section" style="margin-top: 25px;">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">2. Loan Amount & EMI</h4>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Principal Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="principal_amount" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>EMI Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="emi_amount" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                </div>

                <!-- Interest & Tenure -->
                <div class="form-section" style="margin-top: 25px;">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">3. Interest & Tenure</h4>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Interest Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="interest_rate" class="form-control" placeholder="8.5" required>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Interest Type <span class="text-danger">*</span></label>
                            <select name="interest_type" class="form-control" required>
                                <option value="Reducing">Reducing</option>
                                <option value="Flat">Flat</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Tenure (Months) <span class="text-danger">*</span></label>
                            <input type="number" min="1" name="tenure_months" class="form-control" placeholder="60" required>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="form-section" style="margin-top: 25px;">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">4. Dates</h4>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>First EMI Date <span class="text-danger">*</span></label>
                            <input type="date" name="next_emi_date" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Optional Details -->
                <div class="form-section" style="margin-top: 25px;">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">5. Additional Info</h4>
                    <div class="form-group">
                        <label>Calculation Method <span class="text-danger">*</span></label>
                        <select name="balance_calculation_method" class="form-control" required>
                            <option value="AMORTIZATION">Amortization (Auto-calculate Interest)</option>
                            <option value="MANUAL">Manual (I will enter Principal/Interest splits)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any special remarks..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-save-new-loan" disabled>Save Loan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Loan Modal -->
<div class="modal-overlay" id="editLoanModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Edit Loan</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="editLoanForm" novalidate>
            <input type="hidden" name="id">
            <div class="modal-body" style="padding: 20px;">
                <!-- Basic Details -->
                <div class="form-section">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">1. Basic Details</h4>
                    <div class="form-group">
                        <label>Family Member <span class="text-danger">*</span></label>
                        <select name="family_member_id" class="form-control fm-select" required>
                            <option value="">Select Member</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Loan Name <span class="text-danger">*</span></label>
                        <input type="text" name="loan_name" class="form-control" placeholder="e.g. Home Loan" required>
                    </div>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Lender (Bank Name) <span class="text-danger">*</span></label>
                            <input type="text" name="lender" class="form-control" placeholder="e.g. HDFC Bank" required>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Loan Type <span class="text-danger">*</span></label>
                            <select name="loan_type" class="form-control" required>
                                <option value="Personal Loan">Personal Loan</option>
                                <option value="Home Loan">Home Loan</option>
                                <option value="Vehicle Loan">Vehicle Loan</option>
                                <option value="Gold Loan">Gold Loan</option>
                                <option value="Education Loan">Education Loan</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Loan Amount & EMI -->
                <div class="form-section" style="margin-top: 25px;">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">2. Loan Amount & EMI</h4>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Principal Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="principal_amount" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>EMI Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="emi_amount" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                </div>

                <!-- Interest & Tenure -->
                <div class="form-section" style="margin-top: 25px;">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">3. Interest & Tenure</h4>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Interest Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="interest_rate" class="form-control" placeholder="8.5" required>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Interest Type <span class="text-danger">*</span></label>
                            <select name="interest_type" class="form-control" required>
                                <option value="Reducing">Reducing</option>
                                <option value="Flat">Flat</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Tenure (Months) <span class="text-danger">*</span></label>
                            <input type="number" min="1" name="tenure_months" class="form-control" placeholder="60" required>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="form-section" style="margin-top: 25px;">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">4. Dates</h4>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Next EMI Date <span class="text-danger">*</span></label>
                            <input type="date" name="next_emi_date" class="form-control" required>
                        </div>
                    </div>
                </div>

                <!-- Optional Details -->
                <div class="form-section" style="margin-top: 25px;">
                    <h4 class="form-section-title" style="margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; color: var(--primary-color);">5. Additional Info</h4>
                    <div class="form-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="Closed">Closed</option>
                                <option value="Defaulted">Defaulted</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 0;">
                            <label>Calculation Method <span class="text-danger">*</span></label>
                            <select name="balance_calculation_method" class="form-control" required>
                                <option value="AMORTIZATION">Amortization (Auto-calculate)</option>
                                <option value="MANUAL">Manual</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any special remarks..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-save-edit-loan">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Record EMI Modal -->
<div class="modal-overlay" id="addEmiModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Record EMI Payment</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addEmiForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Select Loan</label>
                    <select name="loan_id" class="form-control" required>
                        <option value="">Loading loans...</option>
                    </select>
                </div>
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Total EMI Amount (₹)</label>
                        <input type="number" step="0.01" name="emi_amount" class="form-control" required>
                    </div>
                </div>
                
                <div id="manualEmiFields" style="display: none; background: var(--light-bg); padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <p style="font-size: 0.9em; margin-top: 0; color: var(--text-muted);">Manual Calculation Mode</p>
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Principal Component (₹)</label>
                            <input type="number" step="0.01" name="principal_component" class="form-control">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Interest Component (₹)</label>
                            <input type="number" step="0.01" name="interest_component" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                        <option value="Missed">Missed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <input type="text" name="notes" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Record EMI</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal-overlay" id="settingsModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Analyzer Settings</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="analyzerSettingsForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Debt Payoff Strategy</label>
                    <select name="debt_strategy" class="form-control" required>
                        <option value="avalanche">Avalanche (Highest Interest First)</option>
                        <option value="snowball">Snowball (Smallest Balance First)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Emergency Fund Target (Months)</label>
                    <input type="number" step="0.5" name="emergency_fund_target_months" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Minimum Savings Allocation (₹/mo)</label>
                    <input type="number" step="1" name="minimum_savings_allocation" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Minimum Investment Allocation (₹/mo)</label>
                    <input type="number" step="1" name="minimum_investment_allocation" class="form-control" required>
                </div>
                <!-- Note: Expense classification mapping could be a complex dynamic UI, keeping it simple here for the backend -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal-overlay" id="addAccountModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Bank Account</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addAccountForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Family Member</label>
                    <select name="family_member_id" class="form-control fm-select" required>
                        <option value="">Select Member</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="e.g. HDFC Bank" required>
                </div>
                <div class="form-group">
                    <label>Account Name / Alias</label>
                    <input type="text" name="account_name" class="form-control" placeholder="e.g. Primary Savings" required>
                </div>
                <div class="form-group">
                    <label>Account Type</label>
                    <select name="account_type" class="form-control" required>
                        <option value="Savings">Savings</option>
                        <option value="Current">Current</option>
                        <option value="Salary">Salary</option>
                        <option value="FD">Fixed Deposit</option>
                        <option value="RD">Recurring Deposit</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Opening Balance (₹)</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Record Transaction Modal -->
<div class="modal-overlay" id="addTransactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Record Transaction</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addTransactionForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Account ID</label>
                    <input type="text" name="account_id" class="form-control" placeholder="e.g. ACC-202609-XYZ" required>
                </div>
                <div class="form-group">
                    <label>Transaction Type</label>
                    <select name="type" class="form-control" required>
                        <option value="Transfer">Transfer</option>
                        <option value="Withdrawal">Withdrawal</option>
                        <option value="Deposit">Deposit</option>
                        <option value="Adjustment">Adjustment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount (₹)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. ATM Withdrawal">
                </div>
                <div class="form-group">
                    <label>Reference No. / UTR</label>
                    <input type="text" name="reference" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Record Transaction</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Investment Modal -->
<div class="modal-overlay" id="addInvestmentModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Investment</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addInvestmentForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Family Member</label>
                    <select name="family_member_id" class="form-control fm-select" required>
                        <option value="">Select Member</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Investment Type</label>
                    <select name="investment_type" class="form-control" required>
                        <option value="Stocks">Stocks</option>
                        <option value="Mutual Funds">Mutual Funds</option>
                        <option value="FD">Fixed Deposit (FD)</option>
                        <option value="RD">Recurring Deposit (RD)</option>
                        <option value="Gold">Gold (Paper/Digital)</option>
                        <option value="PPF">PPF</option>
                        <option value="EPF">EPF</option>
                        <option value="NPS">NPS</option>
                        <option value="Crypto">Crypto</option>
                        <option value="Business">Business</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Investment Name</label>
                    <input type="text" name="investment_name" class="form-control" placeholder="e.g. NIFTY 50 Index Fund" required>
                </div>
                <div class="form-group">
                    <label>Invested Amount (₹)</label>
                    <input type="number" step="0.01" name="invested_amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Current Value (₹) [Optional]</label>
                    <input type="number" step="0.01" name="current_value" class="form-control" placeholder="Leave blank if same as invested">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Investment</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Asset Modal -->
<div class="modal-overlay" id="addAssetModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Asset</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addAssetForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Family Member</label>
                    <select name="family_member_id" class="form-control fm-select" required>
                        <option value="">Select Member</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Asset Type</label>
                    <select name="asset_type" class="form-control" required>
                        <option value="Property">Property</option>
                        <option value="Vehicle">Vehicle</option>
                        <option value="Gold">Physical Gold</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Business">Business Asset</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Asset Name</label>
                    <input type="text" name="asset_name" class="form-control" placeholder="e.g. 2BHK Apartment" required>
                </div>
                <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Purchase Value (₹)</label>
                    <input type="number" step="0.01" name="purchase_value" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Current Estimated Value (₹) [Optional]</label>
                    <input type="number" step="0.01" name="current_value" class="form-control" placeholder="Leave blank if unknown">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Asset</button>
            </div>
        </form>
    </div>
</div>

<!-- Set Budget Modal -->
<div class="modal-overlay" id="addBudgetModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Set Monthly Budget</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addBudgetForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Budget Month (e.g. 2026-09)</label>
                    <input type="month" name="month" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control" required>
                        <option value="Rent">Rent</option>
                        <option value="Food">Food</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Education">Education</option>
                        <option value="Medical">Medical</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Insurance">Insurance</option>
                        <option value="Shopping">Shopping</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Family">Family</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Budget Limit Amount (₹)</label>
                    <input type="number" step="0.01" name="budget_amount" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Budget</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Goal Modal -->
<div class="modal-overlay" id="addGoalModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Financial Goal</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addGoalForm">
            <div class="modal-body">
                <div class="form-group">
                    <label>Family Member</label>
                    <select name="family_member_id" class="form-control fm-select" required>
                        <option value="">Select Member</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Goal Name</label>
                    <input type="text" name="goal_name" class="form-control" placeholder="e.g. Europe Vacation, New Car" required>
                </div>
                <div class="form-group">
                    <label>Target Amount (₹)</label>
                    <input type="number" step="0.01" name="target_amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Currently Saved (₹) [Optional]</label>
                    <input type="number" step="0.01" name="current_amount" class="form-control" placeholder="Default is 0">
                </div>
                <div class="form-group">
                    <label>Target Date [Optional]</label>
                    <input type="date" name="target_date" class="form-control">
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Goal</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Family Member Modal -->
<div class="modal-overlay" id="addMemberModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Family Member</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="addMemberForm">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                </div>
                <div class="form-group">
                    <label>Relationship</label>
                    <select name="relationship" class="form-control" required>
                        <option value="Self">Self</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Child">Child</option>
                        <option value="Parent">Parent</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Member</button>
            </div>
        </form>
    </div>
</div>

<!-- View Loan Modal -->
<div class="modal-overlay" id="viewLoanModal">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <h3>Loan Details</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="margin: 0; color: var(--primary-color);" id="vl-loan-name">Loan Name</h2>
                <span id="vl-status" style="padding: 5px 12px; border-radius: 12px; background: #e8f5e9; color: var(--success-color); font-weight: 600; font-size: 0.85em;">Active</span>
            </div>
            
            <div class="responsive-grid" style="gap: 15px; background: var(--light-bg); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div><small class="text-muted">Lender</small><div style="font-weight: 600;" id="vl-lender">-</div></div>
                <div><small class="text-muted">Family Member</small><div style="font-weight: 600;" id="vl-member">-</div></div>
                <div><small class="text-muted">Original Loan</small><div style="font-weight: 600;" id="vl-original-amount">-</div></div>
                <div><small class="text-muted">Outstanding Balance</small><div style="font-weight: bold; color: var(--danger-color); font-size: 1.1em;" id="vl-outstanding">-</div></div>
                <div><small class="text-muted">Interest Rate</small><div style="font-weight: 600;" id="vl-rate">-</div></div>
                <div><small class="text-muted">EMI Amount</small><div style="font-weight: bold; color: var(--warning-color);" id="vl-emi">-</div></div>
                <div><small class="text-muted">Principal Paid</small><div style="font-weight: 600;" id="vl-principal-paid">-</div></div>
                <div><small class="text-muted">Interest Paid</small><div style="font-weight: 600;" id="vl-interest-paid">-</div></div>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9em;">
                    <span>Repayment Progress</span>
                    <span id="vl-progress-text" style="font-weight: bold;">0%</span>
                </div>
                <div style="width: 100%; height: 10px; background: #eee; border-radius: 5px; overflow: hidden;">
                    <div id="vl-progress-bar" style="height: 100%; background: var(--success-color); width: 0%;"></div>
                </div>
            </div>

            <h4 style="margin-bottom: 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Payment History</h4>
            <div style="overflow-x: auto; max-height: 250px; overflow-y: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                    <thead style="position: sticky; top: 0; background: var(--light-bg); box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <tr style="text-align: left;">
                            <th style="padding: 8px;">Date</th>
                            <th style="padding: 8px;">EMI Paid</th>
                            <th style="padding: 8px;">Principal</th>
                            <th style="padding: 8px;">Interest</th>
                        </tr>
                    </thead>
                    <tbody id="emiHistoryTableBody">
                        <tr><td colspan="4" style="text-align: center; padding: 15px;" class="text-muted">Loading EMI history...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: space-between;">
            <button class="btn btn-secondary" id="vl-btn-edit" data-id="">✏️ Edit</button>
            <div>
                <button class="btn btn-cancel modal-close">Close</button>
                <button class="btn btn-primary" id="vl-btn-pay" data-id="" style="background: var(--success-color);">Mark Paid</button>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Mobile Bottom Navigation -->
<nav class="bottom-nav">
    <a href="dashboard.php" class="active">
        <span class="nav-icon">🏠</span>
        <span class="nav-label">Home</span>
    </a>
    <a href="#" data-modal-target="addIncomeModal">
        <span class="nav-icon">💵</span>
        <span class="nav-label">Income</span>
    </a>
    <a href="#" data-modal-target="addExpenseModal">
        <span class="nav-icon">📉</span>
        <span class="nav-label">Expenses</span>
    </a>
    <a href="#" data-modal-target="addLoanModal">
        <span class="nav-icon">🏦</span>
        <span class="nav-label">Loans</span>
    </a>
    <a href="#" id="btn-more-menu" data-modal-target="mobileMoreModal">
        <span class="nav-icon">☰</span>
        <span class="nav-label">More</span>
    </a>
</nav>

<!-- Mobile 'More' Menu Modal (Bottom Sheet) -->
<div class="modal-overlay" id="mobileMoreModal">
    <div class="modal-content bottom-sheet">
        <div class="modal-header">
            <h3>Menu</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <ul class="mobile-more-list">
                <li><a href="#" class="modal-close" data-modal-target="addAccountModal">💳 Bank Accounts</a></li>
                <li><a href="#" class="modal-close" data-modal-target="addInvestmentModal">📈 Investments</a></li>
                <li><a href="#" class="modal-close" data-modal-target="addAssetModal">🏡 Assets</a></li>
                <li><a href="#" class="modal-close" data-modal-target="addBudgetModal">📊 Budget</a></li>
                <li><a href="#" class="modal-close" data-modal-target="addGoalModal">🎯 Goals</a></li>
                <li><a href="#" class="modal-close">📑 Reports</a></li>
            </ul>
        </div>
    </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script src="assets/js/loans.js"></script>
<script src="assets/js/analysis.js"></script>
<script src="assets/js/forms.js"></script>
</body>
</html>
