@extends('base')

@section('header')
    <div class="container d-block">
        <h1 class="h1">Nouvelle commande</h1>
        <p class="mb-0 opacity-75">Fournisseur et articles</p>
    </div>
@endsection

@section('content')
<div class="container mt-4 page-wizard">
    {{-- Stepper visuel --}}
    <div class="d-flex gap-2 mb-3 flex-wrap" id="wizardStepper">
        @foreach([
            [1, 'Infos generales'],
            [2, 'Fournisseur & Articles'],
            [3, 'Recapitulatif']
        ] as $step)
        <div class="flex-fill d-flex align-items-center gap-2 p-3 bg-white rounded-3 {{ $currentStep == $step[0] ? 'border-taupe shadow-sm' : '' }}"
             style="border: 1px solid rgba(0,0,0,.08); {{ $currentStep == $step[0] ? 'border-color: var(--taupe); box-shadow: 0 6px 18px rgba(0,0,0,.08);' : '' }}">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold"
                  style="width: 32px; height: 32px; font-size: 14px; {{ $currentStep == $step[0] ? 'background: var(--navy); color: #fff;' : ($step[0] < $currentStep ? 'background: var(--badge-green); color: #fff;' : 'background: #E6E7EA; color: #1E2233;') }}">
                @if($step[0] < $currentStep)
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/></svg>
                @else
                    {{ $step[0] }}
                @endif
            </span>
            <span class="fw-bold" style="font-size: 14px; color: #1E2233;">{{ $step[1] }}</span>
        </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('orders.store.step2') }}" enctype="multipart/form-data">
        @csrf

        {{-- Fournisseur --}}
        <div class="card border-0 shadow-sm p-4 mb-3" style="border-radius: 16px;">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Fournisseur *</label>
                    <select name="supplier_id" id="supplierSelect" class="form-select @error('supplier_id') is-invalid @enderror" required style="border-radius: 12px; height: 44px;">
                        <option value="">Selectionner...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->getId() }}" @selected(old('supplier_id', $step2['supplier_id'] ?? '') == $supplier->getId())>
                                {{ $supplier->getCompanyName() }}
                            </option>
                        @endforeach
                        <option value="new" @selected(old('supplier_id', $step2['supplier_id'] ?? '') === 'new')>Autre (nouveau fournisseur)...</option>
                    </select>
                    @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Delai moyen --}}
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-uppercase text-muted">Delai moyen (jours) <small class="fw-normal">(optionnel)</small></label>
                    <input type="number" name="supplier_delay" class="form-control" min="0" step="1" placeholder="Ex. 7"
                           value="{{ old('supplier_delay', $step2['supplier_delay'] ?? '') }}"
                           style="border-radius: 12px; height: 44px;">
                </div>

                {{-- Champs nouveau fournisseur --}}
                <div class="col-12" id="newSupplierFields" style="display: none;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted">Nom du fournisseur *</label>
                            <input type="text" name="new_supplier_name" class="form-control @error('new_supplier_name') is-invalid @enderror"
                                   placeholder="Renseigner le nom du fournisseur" value="{{ old('new_supplier_name', $step2['new_supplier_name'] ?? '') }}"
                                   style="border-radius: 12px; height: 44px;">
                            @error('new_supplier_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">Email fournisseur *</label>
                            <input type="email" name="new_supplier_email" class="form-control @error('new_supplier_email') is-invalid @enderror"
                                   placeholder="contact@fournisseur.com" value="{{ old('new_supplier_email', $step2['new_supplier_email'] ?? '') }}"
                                   style="border-radius: 12px; height: 44px;">
                            @error('new_supplier_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">SIRET (14 caracteres) *</label>
                            <input type="text" name="new_supplier_siret" class="form-control @error('new_supplier_siret') is-invalid @enderror"
                                   maxlength="14" value="{{ old('new_supplier_siret', $step2['new_supplier_siret'] ?? '') }}"
                                   style="border-radius: 12px; height: 44px;">
                            @error('new_supplier_siret') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Articles --}}
        <div class="card border-0 shadow-sm p-4 mb-3" style="border-radius: 16px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Articles</h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn text-white btn-sm" id="btnAddRow" style="background: var(--navy); border-radius: 18px;">+ Ajouter une ligne</button>
                    <button type="button" class="btn btn-sm" id="btnClearRows" style="background: #E6E7EA; color: #1E2233; border-radius: 8px;">Vider</button>
                </div>
            </div>

            @error('articles') <div class="alert alert-danger">{{ $message }}</div> @enderror

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-sm" id="articlesTable">
                    <thead style="background: rgba(255,255,255,0.38);">
                        <tr>
                            <th style="min-width: 260px;">Designation</th>
                            <th style="min-width: 110px;">Quantite</th>
                            <th style="min-width: 140px;">Prix unitaire</th>
                            <th style="min-width: 120px;">TVA (%)</th>
                            <th style="min-width: 140px;">Total ligne</th>
                            <th style="min-width: 110px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="articlesBody">
                        {{-- Lignes dynamiques JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Totaux --}}
            <div class="d-flex flex-column align-items-end mt-3 gap-1">
                <div class="d-flex gap-3"><span class="text-muted">Sous-total HT</span><strong id="subtotalHT">0,00 &euro;</strong></div>
                <div class="d-flex gap-3"><span class="text-muted">TVA totale</span><strong id="totalTVA">0,00 &euro;</strong></div>
                <div class="d-flex gap-3 pt-2" style="border-top: 2px dashed #ddd;"><span class="fw-bold">Total TTC</span><strong id="grandTotal" class="fs-5" style="color: var(--navy);">0,00 &euro;</strong></div>
            </div>
        </div>

        {{-- Pieces jointes --}}
        <div class="card border-0 shadow-sm p-4 mb-3" style="border-radius: 16px;">
            <h6 class="fw-bold mb-3">Pieces jointes (optionnel)</h6>
            <div class="mb-2">
                <label class="form-label small fw-bold text-uppercase text-muted">Joindre des documents</label>
                <input type="file" name="quote" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                       style="border-radius: 12px; height: 44px;">
                <small class="text-muted">Formats acceptes : PDF, DOC, PNG, JPG — max 10 Mo par fichier.</small>
            </div>
        </div>

        {{-- N de devis --}}
        <div class="card border-0 shadow-sm p-4 mb-3" style="border-radius: 16px;">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-uppercase text-muted">N° de devis *</label>
                <input type="text" name="quote_num" class="form-control @error('quote_num') is-invalid @enderror" required
                       placeholder="Ex. DEV-2026-001"
                       value="{{ old('quote_num', $step2['quote_num'] ?? '') }}"
                       style="border-radius: 12px; height: 44px;">
                @error('quote_num') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Boutons --}}
        <div class="d-flex gap-2 justify-content-end mt-3 mb-5 flex-wrap">
            <a href="{{ route('orders.create.step1') }}" class="btn" style="background: #E6E7EA; color: #1E2233; border-radius: 8px; min-height: 44px; padding: 10px 16px;">Retour</a>
            <button type="submit" class="btn text-white" style="background: var(--navy); border-radius: 18px; min-height: 44px; padding: 10px 16px;">Continuer</button>
        </div>
    </form>
</div>

<style>
.page-wizard .form-control:focus,
.page-wizard .form-select:focus {
    border-color: #3A6CF0;
    box-shadow: 0 0 0 2px rgba(58, 108, 240, 0.1);
}
.page-wizard .form-control.is-invalid,
.page-wizard .form-select.is-invalid {
    border-color: #E5484D;
    box-shadow: 0 0 0 2px rgba(229, 72, 77, 0.12) inset;
}
@media (max-width: 560px) {
    #wizardStepper { flex-direction: column !important; }
}
</style>

<script>
(function () {
    'use strict';

    var savedArticles = @json($step2['articles'] ?? []);
    var tbody = document.getElementById('articlesBody');
    var btnAdd = document.getElementById('btnAddRow');
    var btnClear = document.getElementById('btnClearRows');
    var supplierSelect = document.getElementById('supplierSelect');
    var newFields = document.getElementById('newSupplierFields');
    var rowIndex = 0;

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

        var inputs = tr.querySelectorAll('.cell-qty, .cell-unit, .cell-tva');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener('input', recalcTotals);
        }
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
            var r = rows[i];
            var qty = parseFloat(r.querySelector('.cell-qty').value) || 0;
            var unit = parseFloat(r.querySelector('.cell-unit').value) || 0;
            var tva = parseFloat(r.querySelector('.cell-tva').value) || 0;
            var lineHT = qty * unit;
            var lineVAT = lineHT * tva / 100;
            r.querySelector('.cell-total').textContent = formatter.format(lineHT + lineVAT) + ' \u20AC';
            totalHT += lineHT;
            totalVAT += lineVAT;
        }
        document.getElementById('subtotalHT').textContent = formatter.format(totalHT) + ' \u20AC';
        document.getElementById('totalTVA').textContent = formatter.format(totalVAT) + ' \u20AC';
        document.getElementById('grandTotal').textContent = formatter.format(totalHT + totalVAT) + ' \u20AC';
    }

    // Bouton ajouter
    btnAdd.addEventListener('click', function () { createRow(); });

    // Bouton vider
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            tbody.innerHTML = '';
            recalcTotals();
        });
    }

    // Init: restaurer ou ligne vide par defaut
    if (savedArticles && savedArticles.length > 0) {
        for (var i = 0; i < savedArticles.length; i++) {
            createRow(savedArticles[i]);
        }
    } else {
        createRow();
    }
})();
</script>
@endsection
