(function () {
    'use strict';

    function init() {
        var tbody = document.getElementById('articlesBody');
        var btnAdd = document.getElementById('btnAddRow');
        var btnClear = document.getElementById('btnClearRows');
        var supplierSelect = document.getElementById('supplierSelect');
        var newFields = document.getElementById('newSupplierFields');
        var rowIndex = 0;

        if (!tbody || !btnAdd) return; // Guard: abort if DOM not ready

        var formatter = new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Toggle nouveau fournisseur
        if (supplierSelect && newFields) {
            supplierSelect.addEventListener('change', function () {
                newFields.style.display = this.value === 'new' ? 'block' : 'none';
            });
            if (supplierSelect.value === 'new') newFields.style.display = 'block';
        }

        function escapeAttr(str) {
            return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function createRow(data) {
            data = data || {};
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td><input type="text" name="articles[' + rowIndex + '][designation]" class="form-control form-control-sm cell-designation" placeholder="Ex: Ordinateur portable" value="' + escapeAttr(data.designation || '') + '" required style="border-radius: 10px;"></td>' +
                '<td><input type="number" name="articles[' + rowIndex + '][quantity]" class="form-control form-control-sm cell-qty" min="1" step="1" value="' + (data.quantity || 1) + '" required style="border-radius: 10px;"></td>' +
                '<td><input type="number" name="articles[' + rowIndex + '][unit_price]" class="form-control form-control-sm cell-unit" min="0" step="0.01" value="' + (data.unit_price || '0') + '" required style="border-radius: 10px;"></td>' +
                '<td><input type="number" name="articles[' + rowIndex + '][vat_rate]" class="form-control form-control-sm cell-tva" min="0" step="0.01" value="' + (data.vat_rate || '20') + '" required style="border-radius: 10px;"></td>' +
                '<td class="cell-total fw-bold align-middle">0,00 \u20AC</td>' +
                '<td class="text-end"><button type="button" class="btn btn-sm btn-remove" style="background: #E6E7EA; color: #E5484D; border-radius: 8px;">Supprimer</button></td>';
            tbody.appendChild(tr);
            rowIndex++;

            // Bind input events
            var inputs = tr.querySelectorAll('.cell-qty, .cell-unit, .cell-tva');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].addEventListener('input', recalcTotals);
            }

            // Bind remove button
            tr.querySelector('.btn-remove').addEventListener('click', function () {
                tr.remove();
                recalcTotals();
            });

            recalcTotals();
        }

        function recalcTotals() {
            var totalHT = 0, totalVAT = 0;
            var rows = document.querySelectorAll('#articlesBody tr');
            for (var i = 0; i < rows.length; i++) {
                var tr = rows[i];
                var qty = parseFloat(tr.querySelector('.cell-qty').value) || 0;
                var unit = parseFloat(tr.querySelector('.cell-unit').value) || 0;
                var tva = parseFloat(tr.querySelector('.cell-tva').value) || 0;
                var lineHT = qty * unit;
                var lineVAT = lineHT * tva / 100;
                var lineTTC = lineHT + lineVAT;
                tr.querySelector('.cell-total').textContent = formatter.format(lineTTC) + ' \u20AC';
                totalHT += lineHT;
                totalVAT += lineVAT;
            }
            document.getElementById('subtotalHT').textContent = formatter.format(totalHT) + ' \u20AC';
            document.getElementById('totalTVA').textContent = formatter.format(totalVAT) + ' \u20AC';
            document.getElementById('grandTotal').textContent = formatter.format(totalHT + totalVAT) + ' \u20AC';
        }

        // Expose for global access
        window.recalcTotals = recalcTotals;

        // Bouton ajouter
        btnAdd.addEventListener('click', function () { createRow(); });

        // Bouton vider
        if (btnClear) {
            btnClear.addEventListener('click', function () {
                tbody.innerHTML = '';
                recalcTotals();
            });
        }

        // Init: restaurer articles sauvegardees ou ajouter ligne vide
        var saved = window._savedArticles;
        if (saved && saved.length > 0) {
            for (var i = 0; i < saved.length; i++) {
                createRow(saved[i]);
            }
        } else {
            createRow();
        }
    }

    // Run when DOM ready, handle both cases
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
