
<div class="theme-switcher-container">
    <button class="theme-toggle-btn" id="themeToggle" title="Theme Switcher">
        <i class="fas fa-palette"></i>
    </button>
    
    <div class="theme-switcher" id="themeSwitcher">
        <button class="theme-btn" onclick="setTheme('dark')" title="Dark Mode">
            <span style="background: linear-gradient(135deg, #000000 50%, #ffffff 50%);"></span>
        </button>
        <button class="theme-btn" onclick="setTheme('light')" title="Light Mode">
            <span style="background: linear-gradient(135deg, #ffffff 50%, #3b82f6 50%);"></span>
        </button>
    </div>
</div>

<style>
.theme-switcher-container {
    position: fixed;
    bottom: 30px;
    right: 20px;
    z-index: 10000;
}

.theme-toggle-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--bg-card);
    border: 2px solid var(--border);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--text);
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
}

.theme-toggle-btn:hover {
    transform: scale(1.1);
    border-color: var(--accent);
    box-shadow: 0 0 12px var(--accent);
}

.theme-switcher {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: none;
    flex-direction: column;
    gap: 10px;
    background: var(--bg-card);
    backdrop-filter: blur(10px);
    padding: 15px;
    border-radius: 15px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    border: 1px solid var(--border);
    animation: slideUp 0.3s ease;
}

.theme-switcher.active {
    display: flex;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.theme-btn {
    width: 45px;
    height: 45px;
    border: 2px solid var(--border);
    border-radius: 50%;
    cursor: pointer;
    padding: 0;
    overflow: hidden;
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
    background: transparent;
}

.theme-btn:hover {
    transform: scale(1.15);
    border-color: var(--accent);
    box-shadow: 0 0 12px var(--accent);
}

.theme-btn span {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 50%;
}
</style>

<script>
const themeToggle = document.getElementById('themeToggle');
const themeSwitcher = document.getElementById('themeSwitcher');

themeToggle.addEventListener('click', () => {
    themeSwitcher.classList.toggle('active');
});

// Close switcher when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.theme-switcher-container')) {
        themeSwitcher.classList.remove('active');
    }
});

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    themeSwitcher.classList.remove('active');
}

// Load saved theme
const savedTheme = localStorage.getItem('theme') || 'dark';
document.documentElement.setAttribute('data-theme', savedTheme);
</script>
