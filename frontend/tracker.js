// Pregnancy Tracker + Feedback Modal
let currentTipWeek = null;

// Log page view for metrics
window.addEventListener('load', () => {
    const f = new FormData();
    f.append('page', 'index');
    fetch('../backend/logpageview.php', { method: 'POST', body: f }).catch(() => {});
});

async function trackPregnancy() {
    const lastPeriod = document.getElementById('lastPeriod').value;
    if (!lastPeriod) {
        alert('Please enter your last menstrual period date.');
        return;
    }

    const form = new FormData();
    form.append('last_period', lastPeriod);

    try {
        const res  = await fetch('../backend/savetracker.php', { method: 'POST', body: form });
        const data = await res.json();

        if (res.status === 401) {
            showLocalResult(lastPeriod);
            return;
        }

        if (data.success) {
            renderResult(data);
        } else {
            document.getElementById('weekInfo').textContent = '⚠ ' + (data.error || 'Could not calculate. Check your date.');
        }
    } catch (e) {
        document.getElementById('weekInfo').textContent = '⚠ Network error. Please try again.';
    }
}

function renderResult(data) {
    const { current_week, due_date, days_pregnant, health_tip } = data;

    const pct = Math.min(100, Math.round((current_week / 42) * 100));
    document.getElementById('progressBar').style.width = pct + '%';

    const due = due_date ? new Date(due_date).toLocaleDateString('en-UG', { day: 'numeric', month: 'long', year: 'numeric' }) : '—';
    document.getElementById('weekInfo').textContent = `${days_pregnant} days pregnant · Due date: ${due}`;
    document.getElementById('pregnancyWeek').textContent = `Week ${current_week} of Pregnancy`;

    const sizes = { 4:'a poppy seed',8:'a raspberry',12:'a lime',16:'an avocado',20:'a banana',24:'an ear of corn',28:'an eggplant',32:'a squash',36:'a head of lettuce',40:'a watermelon' };
    const nearest = Object.keys(sizes).reduce((p,c) => Math.abs(c-current_week) < Math.abs(p-current_week) ? c : p);
    document.getElementById('babySizeText').textContent = `Your baby is about the size of ${sizes[nearest]}`;

    if (health_tip) {
        currentTipWeek = current_week;
        document.getElementById('tipTitle').textContent   = health_tip.title;
        document.getElementById('tipContent').textContent = health_tip.content;
        document.getElementById('healthTipBox').style.display = 'block';
    }
}

function showLocalResult(lastPeriod) {
    const lp   = new Date(lastPeriod);
    const days = Math.floor((new Date() - lp) / 86400000);
    const week = Math.floor(days / 7);
    const due  = new Date(lp); due.setDate(due.getDate() + 280);
    document.getElementById('progressBar').style.width = Math.min(100, Math.round(week/42*100)) + '%';
    document.getElementById('weekInfo').textContent = `Week ${week} preview · Login to save your progress`;
    document.getElementById('pregnancyWeek').textContent = `Week ${week} of Pregnancy`;
}

function openFeedback() {
    document.getElementById('feedbackModal').style.display = 'flex';
    document.getElementById('feedbackMsg').value = '';
    document.getElementById('feedbackStatus').textContent = '';
}

function closeFeedback() {
    document.getElementById('feedbackModal').style.display = 'none';
}

async function submitFeedback() {
    const msg      = document.getElementById('feedbackMsg').value.trim();
    const statusEl = document.getElementById('feedbackStatus');
    if (!msg) { statusEl.textContent = 'Please describe the issue.'; return; }

    statusEl.textContent = 'Submitting...';
    const form = new FormData();
    form.append('type', 'tip_error');
    form.append('message', msg);
    if (currentTipWeek) form.append('week', currentTipWeek);

    try {
        const res  = await fetch('../backend/submitfeedback.php', { method: 'POST', body: form });
        const json = await res.json();
        statusEl.style.color = json.success ? '#1a6640' : '#8b1a1a';
        statusEl.textContent = json.success ? '✓ Thank you! We will review this.' : (json.error || 'Error submitting.');
        if (json.success) setTimeout(closeFeedback, 2000);
    } catch (e) {
        statusEl.style.color = '#8b1a1a';
        statusEl.textContent = 'Network error. Please try again.';
    }
}
