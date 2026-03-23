// tracker.js - Pregnancy Tracker Functionality

function TrackMyPregnancy() {
    // Get the last period date from input - FIXED: Changed ID to match HTML
    const lastPeriodInput = document.getElementById('lastPeriod').value;
    
    // Validate input
    if (!lastPeriodInput) {
        alert('Please select your last period date');
        return;
    }
    
    // Create date objects
    const lastPeriodDate = new Date(lastPeriodInput);
    const today = new Date();
    
    // Validate that last period date is not in the future
    if (lastPeriodDate > today) {
        alert('Last period date cannot be in the future');
        return;
    }
    
    // Calculate due date (40 weeks from last period)
    const dueDate = new Date(lastPeriodDate);
    dueDate.setDate(dueDate.getDate() + 280); // 40 weeks = 280 days
    
    // Calculate current pregnancy week
    const daysPregnant = Math.floor((today - lastPeriodDate) / (1000 * 60 * 60 * 24));
    let currentWeek = Math.floor(daysPregnant / 7);
    let currentDay = daysPregnant % 7;
    
    // Validate if pregnancy is within normal range (0-42 weeks)
    if (daysPregnant < 0) {
        alert('Invalid date selected');
        return;
    }
    
    // Update the UI
    updatePregnancyInfo(currentWeek, currentDay, daysPregnant, dueDate);
    updateBabyGrowth(currentWeek);
    updateProgressBar(currentWeek);
}

function updatePregnancyInfo(week, day, daysPregnant, dueDate) {
    const weekInfoElement = document.getElementById('weekInfo');
    
    // Format due date
    const dueDateFormatted = dueDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Calculate trimester
    let trimester = '';
    if (week <= 13) {
        trimester = 'First Trimester';
    } else if (week <= 27) {
        trimester = 'Second Trimester';
    } else {
        trimester = 'Third Trimester';
    }
    
    // Calculate days until due date
    const today = new Date();
    const daysUntilDue = Math.floor((dueDate - today) / (1000 * 60 * 60 * 24));
    
    // Create status message based on pregnancy stage
    let statusMessage = '';
    if (week < 40) {
        statusMessage = `You have ${daysUntilDue} days until your due date.`;
    } else if (week === 40) {
        statusMessage = 'Your baby is due anytime now!';
    } else if (week > 40) {
        const daysOverdue = daysPregnant - 280;
        statusMessage = `You are ${daysOverdue} days overdue. Please consult your healthcare provider.`;
    }
    
    // Display all information
    weekInfoElement.innerHTML = `
        <strong>Current Stage:</strong> Week ${week}, Day ${day}<br>
        <strong>Trimester:</strong> ${trimester}<br>
        <strong>Estimated Due Date:</strong> ${dueDateFormatted}<br>
        <strong>${statusMessage}</strong>
    `;
}

function updateBabyGrowth(week) {
    const pregnancyWeekElement = document.getElementById('pregnancyWeek');
    const babySizeTextElement = document.getElementById('babySizeText');
    
    // Baby development information by week
    const babyDevelopment = {
        1: { size: 'Poppy seed', length: '0.1 cm', fact: 'Ovulation occurs this week' },
        2: { size: 'Poppy seed', length: '0.1 cm', fact: 'Fertilization happens this week' },
        3: { size: 'Pinhead', length: '0.1-0.2 cm', fact: 'Embryo implants in uterus' },
        4: { size: 'Poppy seed', length: '0.2 cm', fact: 'Heart begins to beat' },
        5: { size: 'Sesame seed', length: '0.3 cm', fact: 'Brain and spinal cord form' },
        6: { size: 'Lentil', length: '0.6 cm', fact: 'Heart beats rhythmically' },
        7: { size: 'Blueberry', length: '1.3 cm', fact: 'Arms and legs begin to form' },
        8: { size: 'Raspberry', length: '1.6 cm', fact: 'Webbed fingers and toes' },
        9: { size: 'Green olive', length: '2.3 cm', fact: 'Eyelids form' },
        10: { size: 'Prune', length: '3.1 cm', fact: 'Vital organs develop' },
        11: { size: 'Lime', length: '4.1 cm', fact: 'Genitals begin to form' },
        12: { size: 'Plum', length: '5.4 cm', fact: 'Fingernails form' },
        13: { size: 'Peach', length: '7.4 cm', fact: 'Baby can make sucking motions' },
        14: { size: 'Lemon', length: '8.7 cm', fact: 'Baby can move around' },
        15: { size: 'Apple', length: '10.1 cm', fact: 'Baby can hear sounds' },
        16: { size: 'Avocado', length: '11.6 cm', fact: 'Eyes move slowly' },
        17: { size: 'Pear', length: '13 cm', fact: 'Baby can hiccup' },
        18: { size: 'Bell pepper', length: '14.2 cm', fact: 'Baby yawns' },
        19: { size: 'Mango', length: '15.3 cm', fact: 'Vernix covers skin' },
        20: { size: 'Banana', length: '16.4 cm', fact: 'Baby swallows more' },
        21: { size: 'Carrot', length: '26.7 cm', fact: 'Baby has regular sleep cycles' },
        22: { size: 'Spaghetti squash', length: '27.8 cm', fact: 'Taste buds form' },
        23: { size: 'Mango', length: '28.9 cm', fact: 'Skin is wrinkled' },
        24: { size: 'Corn', length: '30 cm', fact: 'Lungs develop' },
        25: { size: 'Rutabaga', length: '34.6 cm', fact: 'Hair grows' },
        26: { size: 'Eggplant', length: '35.6 cm', fact: 'Eyes open' },
        27: { size: 'Cauliflower', length: '36.6 cm', fact: 'Brain active' },
        28: { size: 'Eggplant', length: '37.6 cm', fact: 'Baby can blink' },
        29: { size: 'Butternut squash', length: '38.6 cm', fact: 'Kicks and stretches' },
        30: { size: 'Cabbage', length: '39.9 cm', fact: 'Baby can turn head' },
        31: { size: 'Coconut', length: '41.1 cm', fact: 'Rapid brain growth' },
        32: { size: 'Squash', length: '42.4 cm', fact: 'Fingernails reach fingertips' },
        33: { size: 'Pineapple', length: '43.7 cm', fact: 'Baby can detect light' },
        34: { size: 'Cantaloupe', length: '45 cm', fact: 'Immune system develops' },
        35: { size: 'Honeydew', length: '46.2 cm', fact: 'Baby gains weight' },
        36: { size: 'Romaine lettuce', length: '47.4 cm', fact: 'Baby drops lower' },
        37: { size: 'Swiss chard', length: '48.6 cm', fact: 'Full term this week' },
        38: { size: 'Leek', length: '49.8 cm', fact: 'Baby practices breathing' },
        39: { size: 'Mini watermelon', length: '50.7 cm', fact: 'Ready for birth' },
        40: { size: 'Small pumpkin', length: '51.2 cm', fact: 'Baby is full term' },
        41: { size: 'Watermelon', length: '52 cm', fact: 'Baby is overdue' },
        42: { size: 'Watermelon', length: '53 cm', fact: 'Post-term pregnancy' }
    };
    
    // Clamp week to valid range
    const displayWeek = Math.min(Math.max(week, 1), 42);
    
    if (week < 1) {
        pregnancyWeekElement.textContent = 'Not pregnant yet';
        babySizeTextElement.textContent = 'Track your pregnancy after confirmation';
    } else if (week > 42) {
        pregnancyWeekElement.textContent = 'Post-term pregnancy';
        babySizeTextElement.textContent = 'Please consult your healthcare provider';
    } else {
        const development = babyDevelopment[displayWeek] || 
            { size: 'Growing baby', length: 'Varies', fact: 'Baby is developing normally' };
        
        pregnancyWeekElement.innerHTML = `Week ${displayWeek}: Baby is the size of a <strong>${development.size}</strong>`;
        babySizeTextElement.innerHTML = `
            <strong>Length:</strong> ${development.length}<br>
            <strong>Development:</strong> ${development.fact}
        `;
    }
}

function updateProgressBar(week) {
    const progressBar = document.getElementById('progressBar');
    
    // Calculate percentage (40 weeks = 100%)
    let percentage = (week / 40) * 100;
    percentage = Math.min(Math.max(percentage, 0), 100); // Clamp between 0 and 100
    
    // Update progress bar
    progressBar.style.width = percentage + '%';
    
    // Add color coding based on trimester
    if (week <= 13) {
        progressBar.style.backgroundColor = '#4CAF50'; // Green for first trimester
    } else if (week <= 27) {
        progressBar.style.backgroundColor = '#2196F3'; // Blue for second trimester
    } else {
        progressBar.style.backgroundColor = '#FF9800'; // Orange for third trimester
    }
}

// Optional: Add weekly tracking history
function savePregnancyData(lastPeriodDate) {
    const pregnancyData = {
        lastPeriodDate: lastPeriodDate,
        startDate: new Date().toISOString(),
        appointments: []
    };
    
    localStorage.setItem('pregnancyData', JSON.stringify(pregnancyData));
}

// Optional: Load saved pregnancy data - FIXED: Changed ID and function call
function loadPregnancyData() {
    const saved = localStorage.getItem('pregnancyData');
    if (saved) {
        const data = JSON.parse(saved);
        // FIXED: Changed from 'last day of period' to 'lastPeriod'
        document.getElementById('lastPeriod').value = data.lastPeriodDate;
        // FIXED: Changed from trackPregnancy() to TrackMyPregnancy()
        TrackMyPregnancy();
    }
}

// Optional: Add appointment reminder
function addAppointment(date, notes) {
    const data = JSON.parse(localStorage.getItem('pregnancyData') || '{}');
    if (!data.appointments) data.appointments = [];
    
    data.appointments.push({
        date: date,
        notes: notes,
        reminder: true
    });
    
    localStorage.setItem('pregnancyData', JSON.stringify(data));
}

// Optional: Calculate conception date
function calculateConceptionDate(lastPeriodDate) {
    const conceptionDate = new Date(lastPeriodDate);
    conceptionDate.setDate(conceptionDate.getDate() + 14); // Ovulation typically 14 days after LMP
    return conceptionDate;
}

// Optional: Get zodiac sign of expected baby
function getBabyZodiacSign(dueDate) {
    const month = dueDate.getMonth() + 1;
    const day = dueDate.getDate();
    
    const zodiacSigns = [
        { sign: 'Capricorn', start: '12-22', end: '01-19' },
        { sign: 'Aquarius', start: '01-20', end: '02-18' },
        { sign: 'Pisces', start: '02-19', end: '03-20' },
        { sign: 'Aries', start: '03-21', end: '04-19' },
        { sign: 'Taurus', start: '04-20', end: '05-20' },
        { sign: 'Gemini', start: '05-21', end: '06-20' },
        { sign: 'Cancer', start: '06-21', end: '07-22' },
        { sign: 'Leo', start: '07-23', end: '08-22' },
        { sign: 'Virgo', start: '08-23', end: '09-22' },
        { sign: 'Libra', start: '09-23', end: '10-22' },
        { sign: 'Scorpio', start: '10-23', end: '11-21' },
        { sign: 'Sagittarius', start: '11-22', end: '12-21' }
    ];
    
    // Logic to determine zodiac sign based on date
    // This is a simplified version - you'd need proper date comparison logic
    
    return 'Calculate based on due date';
}

// Auto-load saved data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadPregnancyData();
});
