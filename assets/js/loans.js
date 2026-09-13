document.addEventListener('DOMContentLoaded', function() {
    console.log("Loans JS initialized.");

    // Hook into the global filter change event if it exists, or just load data on boot
    const filterMember = document.getElementById('filter-member');
    let currentLoans = [];
    let currentEmis = [];

    
    // Override the dashboard's fetchDashboardData to also fetch Loan data
    const originalFetchDashboardData = window.fetchDashboardData;
    if (originalFetchDashboardData) {
        window.fetchDashboardData = async function() {
            await originalFetchDashboardData();
            await loadLoanDashboardData();
        };
    }

    async function loadLoanDashboardData() {
        try {
            const member_id = filterMember ? filterMember.value : 'ALL';
            
            // 1. Fetch Loans
            const loanResult = await window.apiFetch('loans.php');
            if (loanResult && loanResult.success && loanResult.data) {
                currentLoans = loanResult.data.filter(l => member_id === 'ALL' || l.family_member_id === member_id);
            } else {
                currentLoans = [];
            }

            // 2. Fetch EMIs
            const emiResult = await window.apiFetch('emi.php');
            if (emiResult && emiResult.success && emiResult.data) {
                currentEmis = emiResult.data.filter(e => member_id === 'ALL' || e.family_member_id === member_id);
            } else {
                currentEmis = [];
            }

            renderLoanDashboard(currentLoans, currentEmis);
            populateLoanDropdown(currentLoans);
            
        } catch (e) {
            console.error("Error loading loan dashboard data:", e);
        }
    }

    function renderLoanDashboard(loans, emis) {
        // Calculate KPIs
        let totalOutstanding = 0;
        let totalMonthlyEmi = 0;
        let totalPrincipalPaid = 0;
        let totalInterestPaid = 0;

        const activeLoans = loans.filter(l => l.status === 'Active');

        activeLoans.forEach(l => {
            totalOutstanding += parseFloat(l.outstanding_principal || 0);
            totalMonthlyEmi += parseFloat(l.emi_amount || 0);
            totalPrincipalPaid += parseFloat(l.principal_paid || 0);
            totalInterestPaid += parseFloat(l.interest_paid || 0);
        });

        // Update KPI UI
        const kpiOutstanding = document.getElementById('loan-kpi-outstanding');
        if (kpiOutstanding) kpiOutstanding.textContent = window.formatINR(totalOutstanding);

        const kpiMonthlyEmi = document.getElementById('loan-kpi-monthly-emi');
        if (kpiMonthlyEmi) kpiMonthlyEmi.textContent = window.formatINR(totalMonthlyEmi);

        const kpiPrincipal = document.getElementById('loan-kpi-principal-paid');
        if (kpiPrincipal) kpiPrincipal.textContent = window.formatINR(totalPrincipalPaid);

        const kpiInterest = document.getElementById('loan-kpi-interest-paid');
        if (kpiInterest) kpiInterest.textContent = window.formatINR(totalInterestPaid);

        // Render Upcoming/Overdue EMIs (Dashboard Action Items)
        const upcomingEmisSection = document.getElementById('upcoming-emis-section');
        const upcomingEmisContainer = document.getElementById('upcoming-emis-container');
        
        if (upcomingEmisSection && upcomingEmisContainer) {
            // Filter loans that have a next EMI date
            const loansWithEmiDate = activeLoans.filter(l => l.next_emi_date);
            
            if (loansWithEmiDate.length > 0) {
                // Sort by date ascending
                loansWithEmiDate.sort((a, b) => new Date(a.next_emi_date) - new Date(b.next_emi_date));
                // Take top 3 closest
                const topUpcoming = loansWithEmiDate.slice(0, 3);
                
                upcomingEmisContainer.innerHTML = '';
                topUpcoming.forEach(l => {
                    const card = document.createElement('div');
                    card.style.cssText = 'background: white; border: 1px solid var(--warning-color); border-left: 4px solid var(--warning-color); border-radius: 8px; padding: 15px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);';
                    
                    card.innerHTML = `
                        <div>
                            <h4 style="margin: 0 0 5px 0; color: var(--text-color);">${l.loan_name}</h4>
                            <div style="font-size: 0.9em; color: var(--text-muted);">Due: <strong style="color: var(--danger-color);">${l.next_emi_date}</strong> • ${window.formatINR(l.emi_amount)}</div>
                        </div>
                        <button class="btn btn-primary btn-record-emi-card" data-id="${l.loan_id}" style="padding: 6px 15px; font-size: 0.9em; background: var(--success-color);">Mark Paid</button>
                    `;
                    upcomingEmisContainer.appendChild(card);
                });
                upcomingEmisSection.style.display = 'block';
            } else {
                upcomingEmisSection.style.display = 'none';
            }
        }

        // Render Active Loans Table
        const tbody = document.getElementById('active-loans-table-body');
        if (tbody) {
            if (activeLoans.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 40px 20px;" class="text-muted"><h3>No Active Loans</h3><p>You currently do not have any active loans. Add a loan to get started.</p></td></tr>';
            } else {
                tbody.innerHTML = '';
                activeLoans.forEach(l => {
                    const originalPrincipal = parseFloat(l.principal_amount || 0);
                    const principalPaid = parseFloat(l.principal_paid || 0);
                    const progress = originalPrincipal > 0 ? (principalPaid / originalPrincipal) * 100 : 0;
                    
                    const tr = document.createElement('tr');
                    tr.className = 'loan-row';
                    tr.style.borderBottom = '1px solid var(--border-color)';
                    
                    // Hover effect
                    tr.onmouseover = () => { tr.style.backgroundColor = '#f9f9f9'; };
                    tr.onmouseout = () => { tr.style.backgroundColor = 'transparent'; };

                    tr.innerHTML = `
                        <td data-label="Loan" style="padding: 12px 10px;">
                            <div style="font-weight: 600; color: var(--primary-color);">${l.loan_name}</div>
                            <div style="font-size: 0.85em; color: var(--text-muted);">🏢 ${l.lender}</div>
                        </td>
                        <td data-label="Member" style="padding: 12px 10px;">👤 ${l.family_member_id}</td>
                        <td data-label="Outstanding" class="loan-outstanding-td" style="padding: 12px 10px; font-weight: bold; color: var(--danger-color);">${window.formatINR(l.outstanding_principal)}</td>
                        <td data-label="EMI" style="padding: 12px 10px; font-weight: 600;">${window.formatINR(l.emi_amount)}</td>
                        <td data-label="Next Due" style="padding: 12px 10px; font-size: 0.9em;">${l.next_emi_date || '-'}</td>
                        <td data-label="Progress" style="padding: 12px 10px; min-width: 120px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.8em; margin-bottom: 4px;">
                                <span style="color: var(--text-muted);">Paid</span>
                                <span style="font-weight: bold; color: var(--success-color);">${progress.toFixed(1)}%</span>
                            </div>
                            <div style="background: var(--border-color); border-radius: 4px; height: 6px; width: 100%; overflow: hidden;">
                                <div style="background: var(--success-color); height: 100%; width: ${progress}%; border-radius: 4px;"></div>
                            </div>
                        </td>
                        <td data-label="Status" style="padding: 12px 10px;">
                            <span style="font-size: 0.8em; padding: 4px 10px; border-radius: 12px; background: #e8f5e9; color: var(--success-color); border: 1px solid #c8e6c9; font-weight: 600;">${l.status}</span>
                        </td>
                        <td data-label="Actions" class="loan-actions-td" style="padding: 12px 10px; text-align: right; white-space: nowrap;">
                            <button class="btn btn-primary btn-record-emi-card" data-id="${l.loan_id}" style="padding: 6px 12px; font-size: 0.85em; background: var(--success-color); margin-right: 5px;">✓ Mark Paid</button>
                            <button class="btn btn-secondary btn-edit-loan" data-id="${l.loan_id}" style="padding: 6px 10px; font-size: 0.85em; margin-right: 5px;" title="Edit Loan">Edit</button>
                            <button class="btn btn-secondary btn-view-loan" data-id="${l.loan_id}" style="padding: 6px 10px; font-size: 0.85em; margin-right: 5px;">Details</button>
                            <button class="btn btn-danger btn-delete-loan" data-id="${l.loan_id}" style="padding: 6px; font-size: 0.85em; background: transparent; border: none; cursor: pointer;" title="Delete Loan">🗑️</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }
    }

    function populateLoanDropdown(loans) {
        const select = document.querySelector('select[name="loan_id"]');
        if (select) {
            const currentVal = select.value;
            select.innerHTML = '<option value="">Select a Loan</option>';
            
            const activeLoans = loans.filter(l => l.status === 'Active');
            activeLoans.forEach(loan => {
                const opt = document.createElement('option');
                opt.value = loan.loan_id;
                opt.setAttribute('data-calc-method', loan.balance_calculation_method);
                opt.setAttribute('data-emi-amount', loan.emi_amount);
                opt.textContent = `${loan.loan_name} (${loan.family_member_id}) - ${window.formatINR(loan.emi_amount)}`;
                select.appendChild(opt);
            });
            if (currentVal) select.value = currentVal;
        }
    }

    // Dynamic Form Behavior for EMI recording
    const loanSelect = document.querySelector('select[name="loan_id"]');
    const manualFields = document.getElementById('manualEmiFields');
    const emiAmountInput = document.querySelector('input[name="emi_amount"]');

    if (loanSelect) {
        loanSelect.addEventListener('change', function() {
            const selectedOption = loanSelect.options[loanSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const method = selectedOption.getAttribute('data-calc-method');
                const amount = selectedOption.getAttribute('data-emi-amount');
                
                // Set default EMI amount
                if (emiAmountInput) emiAmountInput.value = amount;
                
                // Show/Hide manual fields
                if (method === 'MANUAL') {
                    if (manualFields) manualFields.style.display = 'block';
                } else {
                    if (manualFields) manualFields.style.display = 'none';
                }
            } else {
                if (manualFields) manualFields.style.display = 'none';
                if (emiAmountInput) emiAmountInput.value = '';
            }
        });
    }

    // Handle Table Actions: Edit, Delete, Record EMI, and View
    const loansTableBody = document.getElementById('active-loans-table-body');
    if (loansTableBody) {
        loansTableBody.addEventListener('click', async function(e) {
            const targetBtn = e.target.closest('button');
            if (!targetBtn) return;

            const loanId = targetBtn.getAttribute('data-id');
            const loan = currentLoans.find(l => l.loan_id === loanId);
            if (!loan) return;

            if (targetBtn.classList.contains('btn-view-loan')) {
                // Populate View Loan Modal
                document.getElementById('vl-loan-name').textContent = loan.loan_name;
                document.getElementById('vl-lender').textContent = loan.lender;
                document.getElementById('vl-member').textContent = loan.family_member_id;
                document.getElementById('vl-original-amount').textContent = window.formatINR(loan.principal_amount);
                document.getElementById('vl-outstanding').textContent = window.formatINR(loan.outstanding_principal);
                document.getElementById('vl-rate').textContent = loan.interest_rate + '% ' + loan.interest_type;
                document.getElementById('vl-emi').textContent = window.formatINR(loan.emi_amount);
                document.getElementById('vl-principal-paid').textContent = window.formatINR(loan.principal_paid);
                document.getElementById('vl-interest-paid').textContent = window.formatINR(loan.interest_paid);

                const original = parseFloat(loan.principal_amount || 0);
                const paid = parseFloat(loan.principal_paid || 0);
                const progress = original > 0 ? (paid / original) * 100 : 0;
                document.getElementById('vl-progress-text').textContent = progress.toFixed(1) + '%';
                document.getElementById('vl-progress-bar').style.width = progress + '%';

                document.getElementById('vl-btn-edit').setAttribute('data-id', loan.loan_id);
                document.getElementById('vl-btn-pay').setAttribute('data-id', loan.loan_id);

                // Populate EMI History Table for this specific loan
                const emiTbody = document.getElementById('emiHistoryTableBody');
                if (emiTbody) {
                    const loanEmis = currentEmis.filter(e => e.loan_id === loanId).sort((a, b) => new Date(b.payment_date) - new Date(a.payment_date));
                    if (loanEmis.length === 0) {
                        emiTbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;" class="text-muted">No EMI history found.</td></tr>';
                    } else {
                        emiTbody.innerHTML = '';
                        loanEmis.forEach(e => {
                            let statusColor = 'var(--text-muted)';
                            if (e.status === 'Paid') statusColor = 'var(--success-color)';
                            else if (e.status === 'Missed') statusColor = 'var(--danger-color)';
                            else if (e.status === 'Pending') statusColor = 'var(--warning-color)';

                            const tr = document.createElement('tr');
                            tr.style.borderBottom = '1px solid var(--border-color)';
                            tr.innerHTML = `
                                <td style="padding: 10px;">${e.payment_date}</td>
                                <td style="padding: 10px; font-weight: bold;">${window.formatINR(e.emi_amount)}</td>
                                <td style="padding: 10px; color: var(--success-color);">${window.formatINR(e.principal_component || 0)}</td>
                                <td style="padding: 10px; color: var(--secondary-color);">${window.formatINR(e.interest_component || 0)}</td>
                            `;
                            emiTbody.appendChild(tr);
                        });
                    }
                }

                document.getElementById('viewLoanModal').classList.add('active');
            } else if (targetBtn.classList.contains('btn-edit-loan')) {
                const form = document.getElementById('editLoanForm');
                if (form) {
                    form.elements['id'].value = loan.loan_id;
                    form.elements['family_member_id'].value = loan.family_member_id;
                    form.elements['loan_name'].value = loan.loan_name;
                    form.elements['lender'].value = loan.lender;
                    form.elements['loan_type'].value = loan.loan_type;
                    form.elements['principal_amount'].value = loan.principal_amount;
                    form.elements['emi_amount'].value = loan.emi_amount;
                    form.elements['interest_rate'].value = loan.interest_rate;
                    form.elements['interest_type'].value = loan.interest_type;
                        form.elements['tenure_months'].value = loan.tenure_months;
                        form.elements['start_date'].value = loan.start_date;
                        form.elements['next_emi_date'].value = loan.next_emi_date || loan.start_date;
                        form.elements['status'].value = loan.status || 'Active';
                        form.elements['balance_calculation_method'].value = loan.balance_calculation_method;
                        form.elements['notes'].value = loan.notes || '';
                        
                        document.getElementById('editLoanModal').classList.add('active');
                        // Trigger validation
                        form.dispatchEvent(new Event('input', { bubbles: true }));
                    }
            } else if (targetBtn.classList.contains('btn-delete-loan')) {
                const loanId = targetBtn.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this loan? This action cannot be undone.')) {
                    try {
                        const response = await window.apiFetch('loans.php', {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: loanId })
                        });
                        if (response && response.success) {
                            alert('Loan deleted successfully.');
                            loadLoanDashboardData(); // Refresh UI
                        } else {
                            alert('Failed to delete loan: ' + (response.error || 'Unknown error'));
                        }
                    } catch (err) {
                        console.error('Error deleting loan:', err);
                        alert('An error occurred while deleting the loan.');
                    }
                }
            } else if (targetBtn.classList.contains('btn-record-emi-card')) {
                const loanId = targetBtn.getAttribute('data-id');
                const modal = document.getElementById('addEmiModal');
                if (modal) {
                    const loanSelect = modal.querySelector('select[name="loan_id"]');
                    if (loanSelect) {
                        loanSelect.value = loanId;
                        // Manually trigger change event to populate fields
                        loanSelect.dispatchEvent(new Event('change'));
                    }
                    modal.classList.add('active');
                }
            }
        });
    }

    // Handle Edit Loan Form Submission
    const editLoanForm = document.getElementById('editLoanForm');
    if (editLoanForm) {
        editLoanForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-save-edit-loan');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            const formData = new FormData(editLoanForm);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await window.apiFetch('loans.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                if (response && response.success) {
                    alert('Loan updated successfully.');
                    document.getElementById('editLoanModal').classList.remove('active');
                    loadLoanDashboardData(); // Refresh UI to recalculate KPIs
                } else {
                    alert('Failed to update loan: ' + (response.error || 'Unknown error'));
                }
            } catch (err) {
                console.error('Error updating loan:', err);
                alert('An error occurred while updating the loan.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    // Setup Form Validation
    function setupFormValidation(formId, submitBtnId) {
        const form = document.getElementById(formId);
        const submitBtn = document.getElementById(submitBtnId);
        if (!form || !submitBtn) return;

        const validateForm = () => {
            let isValid = true;
            if (!form.checkValidity()) {
                isValid = false;
            }
            
            const numberInputs = form.querySelectorAll('input[type="number"]');
            numberInputs.forEach(input => {
                if (input.value !== '' && parseFloat(input.value) <= 0 && input.name !== 'interest_rate') {
                    // Principal, EMI, Tenure must be > 0
                    isValid = false;
                }
            });

            submitBtn.disabled = !isValid;
        };

        form.addEventListener('input', validateForm);
        form.addEventListener('change', validateForm);
        
        // Initial check
        validateForm();
    }

    setupFormValidation('addLoanForm', 'btn-save-new-loan');
    setupFormValidation('editLoanForm', 'btn-save-edit-loan');
    
    // Bind View Loan Modal action buttons
    const vlBtnEdit = document.getElementById('vl-btn-edit');
    const vlBtnPay = document.getElementById('vl-btn-pay');
    
    if (vlBtnEdit) {
        vlBtnEdit.addEventListener('click', function() {
            const loanId = this.getAttribute('data-id');
            const loan = currentLoans.find(l => l.loan_id === loanId);
            if (!loan) return;
            
            document.getElementById('viewLoanModal').classList.remove('active');
            
            const form = document.getElementById('editLoanForm');
            if (form) {
                form.elements['id'].value = loan.loan_id;
                form.elements['family_member_id'].value = loan.family_member_id;
                form.elements['loan_name'].value = loan.loan_name;
                form.elements['lender'].value = loan.lender;
                form.elements['loan_type'].value = loan.loan_type;
                form.elements['principal_amount'].value = loan.principal_amount;
                form.elements['emi_amount'].value = loan.emi_amount;
                form.elements['interest_rate'].value = loan.interest_rate;
                form.elements['interest_type'].value = loan.interest_type;
                form.elements['tenure_months'].value = loan.tenure_months;
                form.elements['start_date'].value = loan.start_date;
                form.elements['next_emi_date'].value = loan.next_emi_date || loan.start_date;
                form.elements['status'].value = loan.status;
                form.elements['notes'].value = loan.notes;
                
                document.getElementById('editLoanModal').classList.add('active');
            }
        });
    }
    
    if (vlBtnPay) {
        vlBtnPay.addEventListener('click', function() {
            const loanId = this.getAttribute('data-id');
            document.getElementById('viewLoanModal').classList.remove('active');
            
            const select = document.querySelector('select[name="loan_id"]');
            if (select) {
                select.value = loanId;
                select.dispatchEvent(new Event('change'));
            }
            
            document.getElementById('addEmiModal').classList.add('active');
        });
    }

    // Boot loader if standalone (fallback if fetchDashboardData is not called)
    if (!originalFetchDashboardData) {
        loadLoanDashboardData();
    }
});
