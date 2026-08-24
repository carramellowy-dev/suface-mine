@props(['withHmCalculator' => false])

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($withHmCalculator)
    const hmAwal = document.getElementById('hmAwal');
    const hmAkhir = document.getElementById('hmAkhir');
    const hmTotal = document.getElementById('hmTotal');
    
    function updateTotal() {
        const awal = parseFloat(hmAwal.value) || 0;
        const akhir = parseFloat(hmAkhir.value) || 0;
        const total = akhir - awal;
        hmTotal.textContent = total.toFixed(1) + ' Jam';
    }
    
    if (hmAwal && hmAkhir && hmTotal) {
        hmAwal.addEventListener('input', updateTotal);
        hmAkhir.addEventListener('input', updateTotal);
        updateTotal(); // initialize total on load
    }
    @endif

    // Color units in maintenance (breakdown/servis) red in dropdown
    const unitSelect = document.getElementById('unitSelect');
    const statusHint = document.getElementById('unitStatusHint');
    if (unitSelect) {
        const statusLabels = {
            'breakdown': 'Breakdown (rusak)',
            'servis': 'Servis (perbaikan)',
            'ready': 'Ready (operasional)',
        };
        Array.from(unitSelect.options).forEach(function(opt) {
            const status = opt.dataset.status;
            if (status === 'breakdown' || status === 'servis') {
                opt.style.color = '#dc2626';
                opt.style.fontWeight = 'bold';
                opt.disabled = true;
                if (!opt.textContent.includes('Sedang Maintenance')) {
                    opt.textContent += ' (Sedang Maintenance - Tidak Bisa Digunakan)';
                }
            }
        });
        unitSelect.addEventListener('change', function() {
            const status = this.options[this.selectedIndex]?.dataset.status || '';
            statusHint.textContent = status ? 'Status: ' + (statusLabels[status] || status) : '';
        });
        
        // Trigger on load for preselected values
        if (unitSelect.value) {
            const status = unitSelect.options[unitSelect.selectedIndex]?.dataset.status || '';
            statusHint.textContent = status ? 'Status: ' + (statusLabels[status] || status) : '';
        }
    }
});
</script>
