document.addEventListener('DOMContentLoaded', function() {
    console.log("Analysis JS initialized.");

    const filterMember = document.getElementById('filter-member');

    // Hook into global fetchDashboardData
    const originalFetchDashboardData = window.fetchDashboardData;
    if (originalFetchDashboardData) {
        window.fetchDashboardData = async function() {
            await originalFetchDashboardData();
            await loadAnalysisData();
        };
    }

    // Load settings on boot
    loadSettings();

    // Setup Settings Form
    const settingsForm = document.getElementById('analyzerSettingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(settingsForm);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const res = await fetch('api/settings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (result.success) {
                    alert('Analyzer Settings saved successfully!');
                    document.querySelector('#settingsModal .modal-close').click();
                    loadAnalysisData(); // Reload analysis
                } else {
                    alert('Error saving settings: ' + result.error);
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred while saving settings.');
            }
        });
    }

    async function loadSettings() {
        try {
            const res = await window.apiFetch('settings.php');
            if (res && res.success && res.data) {
                const s = res.data;
                const form = document.getElementById('analyzerSettingsForm');
                if (form) {
                    if (form.elements['debt_strategy']) form.elements['debt_strategy'].value = s.debt_strategy || 'avalanche';
                    if (form.elements['emergency_fund_target_months']) form.elements['emergency_fund_target_months'].value = s.emergency_fund_target_months || 6;
                    if (form.elements['minimum_savings_allocation']) form.elements['minimum_savings_allocation'].value = s.minimum_savings_allocation || 0;
                    if (form.elements['minimum_investment_allocation']) form.elements['minimum_investment_allocation'].value = s.minimum_investment_allocation || 0;
                }
            }
        } catch(e) {
            console.error("Error loading settings:", e);
        }
    }

    async function loadAnalysisData() {
        try {
            const member_id = filterMember ? filterMember.value : 'ALL';
            const res = await window.apiFetch('analysis.php?member_id=' + encodeURIComponent(member_id));
            
            if (res && res.success && res.data) {
                renderAnalysisDashboard(res.data);
            }
        } catch (e) {
            console.error("Error loading analysis data:", e);
        }
    }

    function renderAnalysisDashboard(data) {
        const { snapshot, ratios, debt, recommendations } = data;

        // 1. Debt-Free Core KPIs
        let totalDebt = 0;
        let totalEMI = 0;
        debt.active_loans.forEach(l => {
            totalDebt += parseFloat(l.outstanding_principal || 0);
            totalEMI += parseFloat(l.emi_amount || 0);
        });

        const elCurrentDebt = document.getElementById('df-current-debt');
        if (elCurrentDebt) elCurrentDebt.textContent = window.formatINR(totalDebt);
        
        const elMonthlyEmi = document.getElementById('df-monthly-emi');
        if (elMonthlyEmi) elMonthlyEmi.textContent = window.formatINR(totalEMI);
        
        const elExtraCapacity = document.getElementById('df-extra-capacity-main');
        if (elExtraCapacity) elExtraCapacity.textContent = window.formatINR(debt.extra_capacity);
        
        const sim = data.simulator;
        let balancedScenario = (sim && sim.scenarios) ? (sim.scenarios.find(s => s.name === 'Balanced') || sim.scenarios[0]) : null;
        
        const elPotentialSaving = document.getElementById('df-potential-saving');
        const elFreedomDate = document.getElementById('df-freedom-date');
        const elStatusText = document.getElementById('df-status-text');

        if (totalDebt <= 0) {
            if (elPotentialSaving) elPotentialSaving.textContent = '₹0';
            if (elFreedomDate) elFreedomDate.textContent = 'Debt Free! 🎉';
            if (elStatusText) elStatusText.textContent = 'Completed 🏆';
        } else if (balancedScenario) {
            if (elPotentialSaving) elPotentialSaving.textContent = window.formatINR(balancedScenario.saved);
            
            let freedomDate = new Date();
            freedomDate.setMonth(freedomDate.getMonth() + balancedScenario.months);
            if (elFreedomDate) elFreedomDate.textContent = freedomDate.toLocaleString('default', { month: 'short', year: 'numeric' });
            
            if (elStatusText) elStatusText.textContent = balancedScenario.months <= 12 ? 'Excellent 🎯' : (balancedScenario.months <= 36 ? 'On Track 🏃‍♂️' : 'Requires Focus 🧗‍♀️');
        } else {
            if (elPotentialSaving) elPotentialSaving.textContent = '-';
            if (elFreedomDate) elFreedomDate.textContent = '-';
            if (elStatusText) elStatusText.textContent = 'Analyzing...';
        }

        // 2. Strategy
        const elStrategyLabel = document.getElementById('df-strategy-label');
        if (elStrategyLabel) elStrategyLabel.textContent = debt.strategy.charAt(0).toUpperCase() + debt.strategy.slice(1);
        
        const explanationMap = {
            'avalanche': 'This strategy minimizes the total interest paid by targeting your highest interest rate debts first.',
            'snowball': 'This strategy builds momentum by targeting your smallest debts first for quick wins.'
        };
        const elStrategyExplanation = document.getElementById('df-strategy-explanation');
        if (elStrategyExplanation) elStrategyExplanation.textContent = explanationMap[debt.strategy] || explanationMap['avalanche'];

        // 3. Recommendations
        const recContainer = document.getElementById('recommendations-container');
        if (recContainer) {
            recContainer.innerHTML = '';
            if (recommendations.length === 0) {
                recContainer.innerHTML = '<div style="padding: 15px; background: var(--light-bg); border-radius: 8px;">No critical recommendations at this time. Keep up the good work!</div>';
            } else {
                const topRecs = recommendations.slice(0, 3);
                topRecs.forEach(r => {
                    let borderCol = 'var(--primary-color)';
                    let icon = '💡';
                    if (r.priority === 'HIGH') { borderCol = 'var(--danger-color)'; icon = '🚨'; }
                    else if (r.priority === 'MEDIUM') { borderCol = 'var(--warning-color)'; icon = '⚠️'; }

                    const div = document.createElement('div');
                    div.style.padding = '15px';
                    div.style.background = 'var(--light-bg)';
                    div.style.borderLeft = `4px solid ${borderCol}`;
                    div.style.borderRadius = '4px';
                    div.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                            <h5 style="margin: 0;">${icon} ${r.issue}</h5>
                            <span style="font-size: 0.75em; padding: 2px 8px; border-radius: 12px; border: 1px solid ${borderCol}; color: ${borderCol}; font-weight: bold;">${r.priority} PRIORITY</span>
                        </div>
                        <p style="margin: 0 0 5px 0; font-size: 0.9em;"><strong>Suggested Action:</strong> ${r.action}</p>
                        <p style="margin: 0; font-size: 0.9em; color: var(--success-color);"><strong>Potential Impact:</strong> ${r.impact}</p>
                    `;
                    recContainer.appendChild(div);
                });
            }
        }

        // Render Scenarios
        const scenBody = document.getElementById('df-scenariosBody');
        if (scenBody && sim && sim.scenarios) {
            scenBody.innerHTML = '';
            sim.scenarios.forEach(s => {
                let extraAmt = 0;
                if (s.name === 'Conservative') extraAmt = debt.extra_capacity * 0.5;
                if (s.name === 'Balanced') extraAmt = debt.extra_capacity * 1.0;
                if (s.name === 'Aggressive') extraAmt = debt.extra_capacity * 1.5;
                
                let highlight = s.name === 'Balanced' ? 'background: #f0f7ff; font-weight: bold;' : '';
                
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid var(--border-color)';
                tr.style.cssText += highlight;
                
                const years = Math.floor(s.months / 12);
                const rMonths = s.months % 12;
                const timeStr = years > 0 ? `${years}y ${rMonths}m` : `${s.months}m`;
                
                tr.innerHTML = `
                    <td style="padding: 10px;">${s.name}</td>
                    <td style="padding: 10px;">${window.formatINR(extraAmt)}</td>
                    <td style="padding: 10px;">${timeStr}</td>
                    <td style="padding: 10px; color: var(--danger-color);">${window.formatINR(s.interest)}</td>
                    <td style="padding: 10px; color: var(--success-color);">${window.formatINR(s.saved)}</td>
                `;
                scenBody.appendChild(tr);
            });
        }
        
        // Render Roadmap
        const tbody = document.getElementById('df-roadmapBody');
        if (tbody && sim && sim.roadmap) {
            tbody.innerHTML = '';
            if (sim.roadmap.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 10px;">You are completely debt-free! 🎉</td></tr>';
            } else {
                sim.roadmap.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid var(--border-color)';
                    tr.innerHTML = `
                        <td style="padding: 10px; font-weight: bold;">${r.month}</td>
                        <td style="padding: 10px; color: var(--primary-color);">${r.target_loan || '-'}</td>
                        <td style="padding: 10px;">${window.formatINR(r.opening_debt)}</td>
                        <td style="padding: 10px;">${window.formatINR(r.regular_emi)}</td>
                        <td style="padding: 10px; color: var(--success-color);">${window.formatINR(r.extra_payment)}</td>
                        <td style="padding: 10px; color: var(--danger-color);">${window.formatINR(r.interest)}</td>
                        <td style="padding: 10px; color: var(--success-color);">${window.formatINR(r.principal)}</td>
                        <td style="padding: 10px; font-weight: bold;">${window.formatINR(r.closing_debt)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }
    }

    if (!originalFetchDashboardData) {
        loadAnalysisData();
    }
});
