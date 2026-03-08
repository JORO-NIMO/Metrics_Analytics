// Postpartum Section JavaScript

// Function to show selected section and hide others
function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });

    // Remove active class from all cards
    const cards = document.querySelectorAll('.category-card');
    cards.forEach(card => {
        card.classList.remove('active');
    });

    // Show selected section
    const selectedSection = document.getElementById(sectionName + '-section');
    if (selectedSection) {
        selectedSection.classList.add('active');
    }

    // Add active class to clicked card
    const clickedCard = event.currentTarget;
    clickedCard.classList.add('active');

    // Scroll to section
    setTimeout(() => {
        selectedSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Hide all sections initially (they're hidden by CSS)
    console.log('Postpartum page loaded');
    
    // Add click handlers to all quick menu items
    document.querySelectorAll('.quick-menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            scrollToElement(targetId);
        });
    });
});

// Scroll to element function
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Track user interactions (for analytics)
function trackSectionView(sectionName) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'view_section', {
            'section_name': sectionName
        });
    }
    console.log('Viewed section:', sectionName);
}

// Save to phone function (enhanced)
function saveToPhone(number, name) {
    if (navigator.share) {
        // For mobile devices with Web Share API
        navigator.share({
            title: 'Save Emergency Contact',
            text: `${name}: ${number}`,
            url: window.location.href,
        })
        .catch(console.error);
    } else {
        // Fallback - create vCard
        const vCard = `BEGIN:VCARD
VERSION:3.0
FN:${name}
TEL:${number}
END:VCARD`;
        
        const blob = new Blob([vCard], { type: 'text/vcard' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${name.replace(/\s+/g, '-')}.vcf`;
        a.click();
        
        alert(`✅ ${name} saved! Check your downloads folder.`);
    }
}

// Print guide function
function printGuide(section) {
    const content = document.getElementById(section + '-section').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Maternal Health Uganda - Postpartum Guide</title>
                <link rel="stylesheet" href="css/style.css">
                <style>
                    body { padding: 20px; font-family: Arial; }
                    .no-print { display: none; }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="postpartum-container">
                    ${content}
                </div>
                <script>
                    window.onload = function() { window.print(); }
                </script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Share guide function
function shareGuide(section) {
    const sectionNames = {
        'recovery': 'Mother\'s Recovery',
        'breastfeeding': 'Breastfeeding Guide',
        'newborn': 'Newborn Care',
        'mental': 'Mental Health Support'
    };
    
    const text = `Check out this ${sectionNames[section]} guide on Maternal Health Uganda`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Maternal Health Uganda',
            text: text,
            url: window.location.href + '#' + section,
        })
        .catch(console.error);
    } else {
        alert('Share this page with other mothers who need this information!');
    }
}
