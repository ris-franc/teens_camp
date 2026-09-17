// Teen Camp 2026 Management System - Interactive Client Scripts

(function () {
    // 1. Theme Management (Dark / Light)
    const THEME_KEY = 'teens_camp_theme';

    function initTheme() {
        const savedTheme = localStorage.getItem(THEME_KEY) || 
            (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(savedTheme);
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem(THEME_KEY, theme);
        
        // Update all theme switch icons
        document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
            const icon = btn.querySelector('i');
            if (icon) {
                if (theme === 'dark') {
                    icon.className = 'bi bi-sun-fill text-warning';
                } else {
                    icon.className = 'bi bi-moon-stars-fill text-dark';
                }
            }
        });
    }

    window.toggleCampTheme = function () {
        const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        applyTheme(next);
    };

    // 2. Live Countdown Timer
    window.initCampCountdown = function (targetIsoDate) {
        const target = new Date(targetIsoDate).getTime();
        if (isNaN(target)) return;

        function update() {
            const now = new Date().getTime();
            const diff = target - now;

            const daysEl = document.getElementById('camp-cd-days');
            const hoursEl = document.getElementById('camp-cd-hours');
            const minsEl = document.getElementById('camp-cd-mins');
            const secsEl = document.getElementById('camp-cd-secs');

            if (!daysEl || !hoursEl || !minsEl || !secsEl) return;

            if (diff <= 0) {
                daysEl.textContent = '00';
                hoursEl.textContent = '00';
                minsEl.textContent = '00';
                secsEl.textContent = '00';
                const statusEl = document.getElementById('camp-cd-status');
                if (statusEl) statusEl.textContent = 'Camp is Live!';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((diff % (1000 * 60)) / 1000);

            daysEl.textContent = String(days).padStart(2, '0');
            hoursEl.textContent = String(hours).padStart(2, '0');
            minsEl.textContent = String(mins).padStart(2, '0');
            secsEl.textContent = String(secs).padStart(2, '0');
        }

        update();
        setInterval(update, 1000);
    };

    // 3. 4-Digit PIN Pad Logic
    window.initPinPad = function (pinInputId, containerId) {
        const hiddenInput = document.getElementById(pinInputId);
        const container = document.getElementById(containerId);
        if (!hiddenInput || !container) return;

        const boxes = container.querySelectorAll('.pin-digit-box');
        const keys = container.querySelectorAll('.pin-key');

        function updateDisplay() {
            const val = hiddenInput.value;
            boxes.forEach((box, idx) => {
                if (idx < val.length) {
                    box.textContent = '•';
                    box.classList.add('filled');
                    box.classList.remove('active');
                } else if (idx === val.length) {
                    box.textContent = '';
                    box.classList.remove('filled');
                    box.classList.add('active');
                } else {
                    box.textContent = '';
                    box.classList.remove('filled', 'active');
                }
            });
        }

        keys.forEach(key => {
            key.addEventListener('click', function () {
                const action = this.getAttribute('data-action');
                let cur = hiddenInput.value;

                if (action === 'backspace') {
                    if (cur.length > 0) {
                        hiddenInput.value = cur.slice(0, -1);
                        updateDisplay();
                    }
                } else if (action === 'clear') {
                    hiddenInput.value = '';
                    updateDisplay();
                } else {
                    const digit = this.getAttribute('data-digit');
                    if (digit !== null && cur.length < 4) {
                        hiddenInput.value = cur + digit;
                        updateDisplay();
                    }
                }
            });
        });

        // Also allow physical keyboard entry
        document.addEventListener('keydown', function (e) {
            if (document.activeElement && document.activeElement.tagName === 'INPUT' && document.activeElement.id !== pinInputId) {
                // Another input has focus
                return;
            }
            if (e.key >= '0' && e.key <= '9') {
                if (hiddenInput.value.length < 4) {
                    hiddenInput.value += e.key;
                    updateDisplay();
                }
            } else if (e.key === 'Backspace') {
                if (hiddenInput.value.length > 0) {
                    hiddenInput.value = hiddenInput.value.slice(0, -1);
                    updateDisplay();
                }
            }
        });

        updateDisplay();
    };

    // 4. Loading Overlay Helpers
    window.showCampLoading = function () {
        const overlay = document.getElementById('camp-loading-overlay');
        if (overlay) overlay.classList.add('active');
    };

    window.hideCampLoading = function () {
        const overlay = document.getElementById('camp-loading-overlay');
        if (overlay) overlay.classList.remove('active');
    };

    // 6. Seamless Notification Click & Decrement Handlers
    window.initNotificationHandlers = function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Clicking an unread notification immediately decrements counter and marks it read
        document.querySelectorAll('.notif-clickable-item').forEach(item => {
            item.addEventListener('click', function (e) {
                const readUrl = this.getAttribute('data-read-url');
                const isRead = this.getAttribute('data-is-read') === '1';
                const targetLink = this.getAttribute('data-target-link');

                if (!isRead && readUrl) {
                    this.setAttribute('data-is-read', '1');
                    this.classList.remove('border-start', 'border-3', 'border-danger');
                    this.classList.add('opacity-75');
                    const titleEl = this.querySelector('.notif-title');
                    if (titleEl) titleEl.classList.remove('text-danger');

                    // Decrement all navbar notification badges
                    document.querySelectorAll('#nav-notif-count').forEach(badge => {
                        let text = badge.textContent.replace('+', '').trim();
                        let count = parseInt(text) || 0;
                        if (count > 1) {
                            count--;
                            badge.textContent = count > 9 ? '9+' : count;
                        } else {
                            badge.remove();
                        }
                    });

                    // Send asynchronous mark as read request
                    fetch(readUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    }).catch(err => console.error('Notification error:', err));
                }

                // If element has target link and user didn't click another button/link inside
                if (targetLink && !e.target.closest('a') && !e.target.closest('button') && !e.target.closest('form')) {
                    window.location.href = targetLink;
                }
            });
        });

        // Mark All Read AJAX
        document.querySelectorAll('.form-mark-all-read').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const actionUrl = this.getAttribute('action');
                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    document.querySelectorAll('#nav-notif-count').forEach(b => b.remove());
                    document.querySelectorAll('.notif-clickable-item').forEach(el => {
                        el.setAttribute('data-is-read', '1');
                        el.classList.remove('border-start', 'border-3', 'border-danger');
                        el.classList.add('opacity-75');
                    });
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.remove();
                }).catch(err => console.error(err));
            });
        });
    };

    // Attach loading states to forms, auto-dismiss alerts, and init notification handlers
    document.addEventListener('DOMContentLoaded', function () {
        initTheme();
        window.initNotificationHandlers();

        // 5. Auto-dismiss all alerts (success, failure, warning, info) after 30 seconds
        document.querySelectorAll('.alert').forEach(function (alertEl) {
            setTimeout(function () {
                try {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                        if (bsAlert) bsAlert.close();
                    } else {
                        alertEl.style.transition = 'opacity 0.5s ease';
                        alertEl.style.opacity = '0';
                        setTimeout(() => alertEl.remove(), 500);
                    }
                } catch (e) {
                    alertEl.remove();
                }
            }, 30000); // 30 seconds
        });

        document.querySelectorAll('form:not(.no-loading)').forEach(form => {
            form.addEventListener('submit', function () {
                if (this.checkValidity()) {
                    window.showCampLoading();
                }
            });
        });
    });
})();
