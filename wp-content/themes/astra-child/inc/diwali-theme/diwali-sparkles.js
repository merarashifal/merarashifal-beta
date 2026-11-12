/**
 * Merarashifal - Grand Diwali Festive Fireworks & Sparkles Engine (v3)
 * High-visibility canvas fireworks, instant launch on load, auto header-anchored toran.
 * Non-blocking (pointer-events: none), pauses on inactive tabs to save CPU/battery.
 */
(function() {
    'use strict';

    var STORAGE_KEY = 'mr_diwali_overlay_enabled';
    var isEnabled = localStorage.getItem(STORAGE_KEY) !== 'false';
    var body = document.body;

    if (!isEnabled) {
        body.classList.add('mr-diwali-disabled');
    }

    // Anchor Toran cleanly below the header bar so menu text is 100% visible
    function attachToranUnderHeader() {
        var toran = document.getElementById('mr-diwali-toran-wrapper');
        if (!toran) return;

        var header = document.querySelector('header.elementor-location-header, header[data-elementor-type="header"], .site-header, #ast-desktop-header, .main-header-bar');
        if (header) {
            header.style.position = 'relative';
            header.appendChild(toran);
            toran.classList.add('mr-toran-attached');
        }
    }

    // Toggle Button Wiring
    function setupToggle() {
        var toggleBtn = document.getElementById('mr-diwali-toggle-btn');
        if (!toggleBtn) return;

        function updateToggleText() {
            var active = !body.classList.contains('mr-diwali-disabled');
            var label = toggleBtn.querySelector('.mr-diwali-toggle-label');
            if (label) {
                label.textContent = active ? 'Diwali Theme: ON' : 'Diwali Theme: OFF';
            }
        }

        updateToggleText();

        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var currentlyDisabled = body.classList.toggle('mr-diwali-disabled');
            var nowActive = !currentlyDisabled;
            localStorage.setItem(STORAGE_KEY, nowActive ? 'true' : 'false');
            updateToggleText();

            if (nowActive && !animationFrameId) {
                lastTime = performance.now();
                nextLaunchTime = lastTime + 200;
                loop(lastTime);
            }
        });
    }

    function initDom() {
        attachToranUnderHeader();
        setupToggle();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDom);
    } else {
        initDom();
    }
    window.addEventListener('load', attachToranUnderHeader);

    // Canvas Fireworks Engine
    var canvas = document.getElementById('mr-diwali-sparkles-canvas');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    if (!ctx) return;

    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    var width = window.innerWidth;
    var height = window.innerHeight;
    var dpr = Math.min(window.devicePixelRatio || 1, 2);

    function resizeCanvas() {
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = Math.floor(width * dpr);
        canvas.height = Math.floor(height * dpr);
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(dpr, dpr);
    }

    window.addEventListener('resize', debounce(resizeCanvas, 150));
    resizeCanvas();

    // High-visibility Festive Palette: Rich colors that pop against light and dark backgrounds
    var PALETTES = [
        ['#ff2a6d', '#ff5e7e', '#ffe066', '#ffffff'], // Ruby & Radiant Gold
        ['#ff6b00', '#ffa200', '#ffd000', '#ffffff'], // Deep Saffron & Marigold
        ['#d946ef', '#ec4899', '#fde047', '#38bdf8'], // Royal Magenta & Cyan
        ['#10b981', '#34d399', '#fde047', '#ffffff'], // Emerald Sparkle
        ['#f59e0b', '#fbbf24', '#fef08a', '#d97706']  // Pure Golden Kamani
    ];

    var rockets = [];
    var particles = [];
    var ambientSparkles = [];

    var isMobile = width < 768;
    var MAX_PARTICLES = isMobile ? 70 : 150;
    var MAX_AMBIENT = isMobile ? 18 : 36;

    // Helper: Draw 4-point sparkle star
    function drawStar(ctx, cx, cy, spikes, outerRadius, innerRadius, color, alpha) {
        ctx.save();
        ctx.globalAlpha = Math.max(0, Math.min(1, alpha));
        ctx.fillStyle = color;
        ctx.shadowColor = color;
        ctx.shadowBlur = 6;
        ctx.beginPath();
        var rot = Math.PI / 2 * 3;
        var x = cx;
        var y = cy;
        var step = Math.PI / spikes;

        ctx.moveTo(cx, cy - outerRadius);
        for (var i = 0; i < spikes; i++) {
            x = cx + Math.cos(rot) * outerRadius;
            y = cy + Math.sin(rot) * outerRadius;
            ctx.lineTo(x, y);
            rot += step;

            x = cx + Math.cos(rot) * innerRadius;
            y = cy + Math.sin(rot) * innerRadius;
            ctx.lineTo(x, y);
            rot += step;
        }
        ctx.lineTo(cx, cy - outerRadius);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
    }

    // Rocket (Skyshot Ascender)
    function Rocket(startX, startY, targetX, targetY, palette) {
        this.x = startX;
        this.y = startY;
        this.targetX = targetX;
        this.targetY = targetY;
        this.palette = palette;

        var dx = targetX - startX;
        var dy = targetY - startY;
        var dist = Math.sqrt(dx * dx + dy * dy);
        var speed = 9 + Math.random() * 3.5;

        this.vx = (dx / dist) * speed;
        this.vy = (dy / dist) * speed;
        this.trail = [];
        this.exploded = false;
        this.color = '#fffbeb';
    }

    Rocket.prototype.update = function() {
        this.trail.push({ x: this.x, y: this.y, alpha: 1 });
        if (this.trail.length > 7) this.trail.shift();

        this.x += this.vx;
        this.y += this.vy;

        if (this.y <= this.targetY || this.vy >= 0) {
            this.explode();
        }
    };

    Rocket.prototype.draw = function() {
        ctx.save();
        for (var i = 0; i < this.trail.length; i++) {
            var pt = this.trail[i];
            var factor = (i + 1) / this.trail.length;
            ctx.beginPath();
            ctx.arc(pt.x, pt.y, 2 * factor, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(251, 191, 36, ' + (factor * 0.9) + ')';
            ctx.shadowColor = '#f59e0b';
            ctx.shadowBlur = 6;
            ctx.fill();
        }

        ctx.beginPath();
        ctx.arc(this.x, this.y, 3, 0, Math.PI * 2);
        ctx.fillStyle = '#ffffff';
        ctx.shadowColor = '#fbbf24';
        ctx.shadowBlur = 10;
        ctx.fill();
        ctx.restore();
    };

    Rocket.prototype.explode = function() {
        this.exploded = true;
        var count = isMobile ? (22 + Math.floor(Math.random() * 10)) : (36 + Math.floor(Math.random() * 16));
        var burstStyle = Math.random() > 0.4 ? 'circle' : 'willow';

        for (var i = 0; i < count; i++) {
            if (particles.length >= MAX_PARTICLES) {
                particles.shift();
            }
            particles.push(new Particle(this.x, this.y, this.palette, burstStyle));
        }
    };

    // Firework Particle / Sparkle
    function Particle(x, y, palette, style) {
        this.x = x;
        this.y = y;
        this.style = style;
        this.color = palette[Math.floor(Math.random() * palette.length)];

        var angle = Math.random() * Math.PI * 2;
        var speed = style === 'willow' 
            ? (1.5 + Math.random() * 3.2) 
            : (2.5 + Math.random() * 5.5);

        this.vx = Math.cos(angle) * speed;
        this.vy = Math.sin(angle) * speed;

        this.gravity = style === 'willow' ? 0.075 : 0.055;
        this.friction = 0.965;
        this.alpha = 1;
        this.decay = 0.014 + Math.random() * 0.016;
        this.radius = 2.4 + Math.random() * 2.2;
        this.isStar = Math.random() > 0.4;
        this.flicker = Math.random() * Math.PI * 2;
        this.history = [];
    }

    Particle.prototype.update = function() {
        this.history.push({ x: this.x, y: this.y });
        if (this.history.length > 3) this.history.shift();

        this.vx *= this.friction;
        this.vy = (this.vy * this.friction) + this.gravity;
        this.x += this.vx;
        this.y += this.vy;
        this.alpha -= this.decay;
        this.flicker += 0.35;
    };

    Particle.prototype.draw = function() {
        var a = Math.max(0, this.alpha * (0.7 + 0.3 * Math.sin(this.flicker)));
        if (a <= 0) return;

        if (this.history.length > 1) {
            ctx.save();
            ctx.beginPath();
            ctx.moveTo(this.history[0].x, this.history[0].y);
            ctx.lineTo(this.x, this.y);
            ctx.strokeStyle = this.color;
            ctx.globalAlpha = a * 0.45;
            ctx.lineWidth = this.radius * 0.8;
            ctx.stroke();
            ctx.restore();
        }

        if (this.isStar) {
            drawStar(ctx, this.x, this.y, 4, this.radius * 1.8, this.radius * 0.5, this.color, a);
        } else {
            ctx.save();
            ctx.globalAlpha = a;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.shadowColor = this.color;
            ctx.shadowBlur = 8;
            ctx.fill();
            ctx.restore();
        }
    };

    // Ambient Floating Golden Sparkles
    function AmbientSparkle() {
        this.reset(true);
    }

    AmbientSparkle.prototype.reset = function(initial) {
        this.x = Math.random() * width;
        this.y = initial ? (Math.random() * height) : -12;
        this.vy = 0.4 + Math.random() * 0.7;
        this.vx = (Math.random() - 0.5) * 0.4;
        this.radius = 1.4 + Math.random() * 1.6;
        this.alpha = 0.3 + Math.random() * 0.65;
        this.flicker = Math.random() * Math.PI * 2;
        this.flickerSpeed = 0.04 + Math.random() * 0.05;
        this.color = Math.random() > 0.3 ? '#f59e0b' : '#ef4444';
        this.isStar = Math.random() > 0.5;
    };

    AmbientSparkle.prototype.update = function() {
        this.y += this.vy;
        this.x += this.vx;
        this.flicker += this.flickerSpeed;
        if (this.y > height + 15) {
            this.reset(false);
        }
    };

    AmbientSparkle.prototype.draw = function() {
        var a = Math.max(0, this.alpha * (0.6 + 0.4 * Math.sin(this.flicker)));
        if (this.isStar) {
            drawStar(ctx, this.x, this.y, 4, this.radius * 2, this.radius * 0.6, this.color, a);
        } else {
            ctx.save();
            ctx.globalAlpha = a;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.shadowColor = this.color;
            ctx.shadowBlur = 5;
            ctx.fill();
            ctx.restore();
        }
    };

    for (var a = 0; a < MAX_AMBIENT; a++) {
        ambientSparkles.push(new AmbientSparkle());
    }

    function triggerRocketBurst() {
        var startX = width * (0.15 + Math.random() * 0.7);
        var startY = height + 10;
        var targetX = startX + (Math.random() - 0.5) * (width * 0.3);
        var targetY = height * (0.12 + Math.random() * 0.32);
        var palette = PALETTES[Math.floor(Math.random() * PALETTES.length)];

        rockets.push(new Rocket(startX, startY, targetX, targetY, palette));
    }

    // Instant initial double burst on page load
    triggerRocketBurst();
    setTimeout(function() {
        triggerRocketBurst();
    }, 450);

    var nextLaunchTime = performance.now() + 1200;
    var animationFrameId = null;
    var lastTime = performance.now();

    function loop(now) {
        if (body.classList.contains('mr-diwali-disabled')) {
            ctx.clearRect(0, 0, width, height);
            animationFrameId = null;
            return;
        }

        animationFrameId = requestAnimationFrame(loop);
        ctx.clearRect(0, 0, width, height);

        for (var i = 0; i < ambientSparkles.length; i++) {
            ambientSparkles[i].update();
            ambientSparkles[i].draw();
        }

        if (now >= nextLaunchTime) {
            triggerRocketBurst();
            var delay = isMobile ? (1800 + Math.random() * 1200) : (1100 + Math.random() * 1100);
            nextLaunchTime = now + delay;
        }

        for (var r = rockets.length - 1; r >= 0; r--) {
            rockets[r].update();
            rockets[r].draw();
            if (rockets[r].exploded) {
                rockets.splice(r, 1);
            }
        }

        for (var p = particles.length - 1; p >= 0; p--) {
            particles[p].update();
            particles[p].draw();
            if (particles[p].alpha <= 0) {
                particles.splice(p, 1);
            }
        }
    }

    if (!body.classList.contains('mr-diwali-disabled')) {
        animationFrameId = requestAnimationFrame(loop);
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
        } else {
            if (!animationFrameId && !body.classList.contains('mr-diwali-disabled')) {
                lastTime = performance.now();
                nextLaunchTime = lastTime + 300;
                animationFrameId = requestAnimationFrame(loop);
            }
        }
    });

    function debounce(fn, ms) {
        var timer;
        return function() {
            clearTimeout(timer);
            timer = setTimeout(fn, ms);
        };
    }
})();
