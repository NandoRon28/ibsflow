document.addEventListener('DOMContentLoaded', function() {
    // Filter Pesantren untuk direktori.php
    window.filterPesantren = function() {
        const category = document.getElementById('category')?.value?.toLowerCase();
        const cards = document.querySelectorAll('.directory-card');
        
        // Debugging: Periksa apakah elemen ditemukan
        if (!category || !cards.length) {
            console.error('Filter gagal: kategori atau kartu tidak ditemukan.', { category, cardCount: cards.length });
            return;
        }

        console.log('Memulai filter pesantren dengan kategori:', category);

        cards.forEach(card => {
            const cardCategory = card.getAttribute('data-category')?.toLowerCase();
            
            // Debugging: Periksa nilai kategori pada kartu
            if (!cardCategory) {
                console.warn('Kartu tidak memiliki data-category:', card);
                return;
            }

            console.log('Memproses kartu dengan kategori:', cardCategory);

            // Terapkan filter langsung pada elemen kartu
            card.style.display = (category === 'all' || cardCategory === category) ? 'block' : 'none';
        });
    };

    // Setel bahasa default dari server ke localStorage jika belum ada
    if (!localStorage.getItem('selectedLang')) {
        localStorage.setItem('selectedLang', '<?php echo htmlspecialchars($selectedLang); ?>');
    }

    // Debugging untuk memastikan translations.js dimuat
    if (typeof changeLanguage === 'undefined') {
        console.error('File translations.js tidak dimuat atau fungsi changeLanguage tidak ditemukan.');
    }
});