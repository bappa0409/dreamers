<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dreamers Association — সঞ্চয়, বিনিয়োগ, ভবিষ্যৎ</title>
<meta name="description" content="Dreamers Association — a member-owned savings, investment and land-development cooperative.">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          ink:        '#1A2B22',
          paper:      '#EDE7D3',
          paperdark:  '#E1D9BE',
          paperdeep:  '#D8CEAE',
          teal:       { deep: '#0B3B34', DEFAULT: '#155346', light: '#1F6E5C' },
          gold:       { DEFAULT: '#B98D2E', light: '#D9B45C', pale: '#EFDFAE' },
          stamp:      '#9C3B2E',
        },
        fontFamily: {
          display: ['Space Grotesk', 'sans-serif'],
          body: ['Inter', 'sans-serif'],
          mono: ['IBM Plex Mono', 'monospace'],
          bn: ['Hind Siliguri', 'sans-serif'],
        },
      }
    }
  }
</script>

<style>
  html { scroll-behavior: smooth; }
  body {
    background-color: #EDE7D3;
    background-image:
      radial-gradient(circle at 1px 1px, rgba(26,43,34,0.06) 1px, transparent 0);
    background-size: 22px 22px;
  }
  .stitch {
    background-image: repeating-linear-gradient(to bottom, transparent 0 8px, rgba(185,141,46,0.55) 8px 9px);
  }
  .stitch-h {
    background-image: repeating-linear-gradient(to right, transparent 0 8px, rgba(185,141,46,0.55) 8px 9px);
  }
  .perforate {
    background-image: radial-gradient(circle, #EDE7D3 3px, transparent 3.5px);
    background-size: 16px 16px;
    background-position: left center;
  }
  .ledger-rule {
    background-image: repeating-linear-gradient(to bottom, transparent 0 39px, rgba(26,43,34,0.14) 39px 40px);
  }
  .tab-num {
    writing-mode: vertical-rl;
    text-orientation: mixed;
  }
  .reveal {
    opacity: 0;
    transform: translateY(18px);
    transition: opacity 0.7s ease, transform 0.7s ease;
  }
  .reveal.in {
    opacity: 1;
    transform: translateY(0);
  }
  .stamp-pop {
    opacity: 0;
    transform: scale(1.6) rotate(-14deg);
    transition: opacity 0.5s ease 0.15s, transform 0.5s cubic-bezier(.34,1.56,.64,1) 0.15s;
  }
  .stamp-pop.in {
    opacity: 1;
    transform: scale(1) rotate(-8deg);
  }
  .grain-card {
    box-shadow: 0 1px 0 rgba(26,43,34,0.06), 0 12px 24px -16px rgba(11,59,52,0.35);
  }
  .taped {
    box-shadow: 0 10px 22px -10px rgba(26,43,34,0.4);
  }
  .taped::before {
    content: "";
    position: absolute;
    top: -10px; left: 50%;
    transform: translateX(-50%) rotate(-2deg);
    width: 60px; height: 22px;
    background: rgba(217,180,92,0.55);
    border: 1px solid rgba(185,141,46,0.4);
  }
  .font-display { letter-spacing: -0.02em; }
  ::selection { background: #D9B45C; color: #1A2B22; }
  .noscroll { overflow: hidden; }
  .count-up { font-variant-numeric: tabular-nums; }
  @media (prefers-reduced-motion: reduce) {
    .reveal, .stamp-pop { transition: none !important; opacity: 1 !important; transform: none !important; }
    html { scroll-behavior: auto; }
  }
  .marquee-track {
    animation: marquee 26s linear infinite;
  }
  @keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
  }
  .binding {
    background-image:
      repeating-linear-gradient(to bottom, transparent 0 26px, rgba(11,59,52,0.9) 26px 27px),
      linear-gradient(to right, #0B3B34, #0B3B34);
  }
  .focus-ring:focus-visible {
    outline: 3px solid #B98D2E;
    outline-offset: 3px;
  }
</style>
</head>

<body class="text-ink font-body antialiased">

<!-- decorative spine binding, desktop only -->
<div class="hidden lg:block fixed left-0 top-0 h-full w-[10px] binding z-40" aria-hidden="true"></div>

<!-- ============ HEADER ============ -->
<header id="top" class="sticky top-0 z-50 bg-teal-deep/95 backdrop-blur text-paper shadow-[0_2px_0_rgba(185,141,46,0.5)] lg:pl-[10px]">
  <div class="max-w-7xl mx-auto px-5 sm:px-8">
    <div class="flex items-center justify-between h-16 sm:h-[72px]">
      <a href="#top" class="flex items-center gap-3 focus-ring rounded">
        <span class="w-9 h-9 rounded-full border-2 border-gold flex items-center justify-center font-display font-semibold text-gold text-lg">D</span>
        <span class="font-display text-lg sm:text-xl tracking-wide">Dreamers <span class="text-gold">Association</span></span>
      </a>

      <nav class="hidden lg:flex items-center gap-7 font-medium text-sm text-paper/90">
        <a href="#about" class="nav-link hover:text-gold transition-colors focus-ring rounded">About</a>
        <a href="#membership" class="nav-link hover:text-gold transition-colors focus-ring rounded">Membership</a>
        <a href="#investments" class="nav-link hover:text-gold transition-colors focus-ring rounded">Investments</a>
        <a href="#land" class="nav-link hover:text-gold transition-colors focus-ring rounded">Land</a>
        <a href="#projects" class="nav-link hover:text-gold transition-colors focus-ring rounded">Projects</a>
        <a href="#transparency" class="nav-link hover:text-gold transition-colors focus-ring rounded">Transparency</a>
        <a href="#news" class="nav-link hover:text-gold transition-colors focus-ring rounded">News</a>
        <a href="#contact" class="nav-link hover:text-gold transition-colors focus-ring rounded">Contact</a>
      </nav>

      <div class="flex items-center gap-3">
        <a href="#" class="hidden sm:inline-flex items-center gap-2 bg-gold text-teal-deep font-semibold text-sm px-4 py-2 rounded-full hover:bg-gold-light transition-colors focus-ring">
          Member Login
        </a>
        <button id="menuBtn" aria-label="Open menu" aria-expanded="false" class="lg:hidden w-10 h-10 flex items-center justify-center border border-paper/30 rounded-md focus-ring">
          <svg id="menuIconOpen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg id="menuIconClose" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- mobile nav -->
  <div id="mobileNav" class="lg:hidden hidden bg-teal-deep border-t border-gold/30">
    <nav class="flex flex-col px-5 py-4 gap-1 font-medium text-paper/90">
      <a href="#about" class="mobile-link py-2.5 border-b border-paper/10 focus-ring rounded">About</a>
      <a href="#membership" class="mobile-link py-2.5 border-b border-paper/10 focus-ring rounded">Membership</a>
      <a href="#investments" class="mobile-link py-2.5 border-b border-paper/10 focus-ring rounded">Investments</a>
      <a href="#land" class="mobile-link py-2.5 border-b border-paper/10 focus-ring rounded">Land</a>
      <a href="#projects" class="mobile-link py-2.5 border-b border-paper/10 focus-ring rounded">Projects</a>
      <a href="#transparency" class="mobile-link py-2.5 border-b border-paper/10 focus-ring rounded">Transparency</a>
      <a href="#news" class="mobile-link py-2.5 border-b border-paper/10 focus-ring rounded">News</a>
      <a href="#contact" class="mobile-link py-2.5 focus-ring rounded">Contact</a>
      <a href="#" class="mt-3 inline-flex justify-center items-center gap-2 bg-gold text-teal-deep font-semibold text-sm px-4 py-2.5 rounded-full focus-ring">Member Login</a>
    </nav>
  </div>
</header>

<main class="lg:pl-[10px]">

<!-- ============ HERO — the passbook cover ============ -->
<section id="home" class="relative bg-teal-deep text-paper overflow-hidden">
  <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 2px 2px, #EFDFAE 1.4px, transparent 0); background-size: 26px 26px;" aria-hidden="true"></div>
  <div class="max-w-7xl mx-auto px-5 sm:px-8 pt-16 pb-20 sm:pt-24 sm:pb-28 relative">
    <div class="grid lg:grid-cols-5 gap-12 items-center">
      <div class="lg:col-span-3">
        <p class="font-bn text-gold-light text-lg mb-3 tracking-wide">স্বপ্ন থেকে সঞ্চয়, সঞ্চয় থেকে সম্পদ</p>
        <h1 class="font-display text-4xl sm:text-5xl md:text-6xl leading-[1.08] font-semibold">
          A passbook we keep <span class="text-gold italic">together.</span>
        </h1>
        <p class="mt-6 text-paper/80 text-lg max-w-xl leading-relaxed">
          Dreamers Association is a member-owned savings and investment cooperative. Every month, members contribute a little — together, that becomes land, projects, and a shared future.
        </p>
        <div class="mt-9 flex flex-wrap items-center gap-4">
          <a href="#membership" class="focus-ring inline-flex items-center gap-2 bg-gold text-teal-deep font-semibold px-6 py-3.5 rounded-full hover:bg-gold-light transition-colors">
            Become a Member
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </a>
          <a href="#about" class="focus-ring inline-flex items-center gap-2 border border-paper/40 px-6 py-3.5 rounded-full hover:border-gold hover:text-gold transition-colors font-medium">
            Read Our Story
          </a>
        </div>
      </div>

      <!-- the "cover" seal -->
      <div class="lg:col-span-2 flex justify-center lg:justify-end">
        <div class="stamp-pop relative w-56 h-56 sm:w-64 sm:h-64 rounded-full border-[3px] border-gold flex items-center justify-center text-center p-6" data-reveal>
          <div class="absolute inset-3 rounded-full border border-dashed border-gold/60"></div>
          <div>
            <p class="font-bn text-gold-light text-sm mb-1">ড্রিমার্স এসোসিয়েশন</p>
            <p class="font-display text-2xl text-gold leading-tight">Established<br>2019</p>
            <p class="mt-2 text-[11px] tracking-[0.2em] text-paper/70 uppercase">Member Owned · Since 2019</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ledger stats strip -->
  <div class="border-t border-gold/30 bg-teal-deep/60">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8">
      <div>
        <p class="font-mono count-up text-3xl sm:text-4xl text-gold" data-target="612">0</p>
        <p class="text-paper/70 text-xs sm:text-sm mt-1 uppercase tracking-wider">Active Members</p>
      </div>
      <div>
        <p class="font-mono count-up text-3xl sm:text-4xl text-gold" data-target="18" data-prefix="৳" data-suffix=" L+">0</p>
        <p class="text-paper/70 text-xs sm:text-sm mt-1 uppercase tracking-wider">Monthly Savings Pool</p>
      </div>
      <div>
        <p class="font-mono count-up text-3xl sm:text-4xl text-gold" data-target="7" data-suffix=" Bigha">0</p>
        <p class="text-paper/70 text-xs sm:text-sm mt-1 uppercase tracking-wider">Land Under Ownership</p>
      </div>
      <div>
        <p class="font-mono count-up text-3xl sm:text-4xl text-gold" data-target="5">0</p>
        <p class="text-paper/70 text-xs sm:text-sm mt-1 uppercase tracking-wider">Active Projects</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ 01 ABOUT ============ -->
<section id="about" class="relative">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;01 — ABOUT</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Who we are</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3 max-w-2xl">About Dreamers Association</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-10 mt-10">
          <p class="reveal text-ink/80 leading-relaxed" data-reveal>
            Dreamers Association began in 2019 with a simple idea: a small group of friends and neighbours saving a fixed amount together, every month, without fail. What started as a shared notebook of contributions has grown into a structured, member-governed cooperative — one that pools savings into land, projects, and long-term investments that no single member could build alone.
          </p>
          <p class="reveal text-ink/80 leading-relaxed" data-reveal>
            We are not a bank and we are not an investment fund — we are our members. Every taka saved is recorded, every purchase is voted on, and every member can see exactly where the association's money stands. Trust is our founding currency, and transparency is how we keep it.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ 02 MISSION / VISION / OBJECTIVES ============ -->
<section id="mission" class="bg-paperdark/60 border-y border-teal/10">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;02 — PURPOSE</span>
      </div>
      <div class="lg:col-span-11">
        <div class="grid md:grid-cols-3 gap-6">
          <div class="reveal grain-card bg-paper rounded-md p-7 border border-teal/10" data-reveal>
            <div class="w-11 h-11 rounded-full bg-teal-deep flex items-center justify-center mb-5">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.97-4-8-7.5-8-11a8 8 0 1116 0c0 3.5-3.03 7-8 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            </div>
            <h3 class="font-display text-xl font-semibold">Mission</h3>
            <p class="mt-3 text-ink/75 text-sm leading-relaxed">To build a disciplined culture of collective saving and responsible investment, so members can achieve financial goals that are difficult to reach alone.</p>
          </div>
          <div class="reveal grain-card bg-paper rounded-md p-7 border border-teal/10" data-reveal>
            <div class="w-11 h-11 rounded-full bg-teal-deep flex items-center justify-center mb-5">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7S2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <h3 class="font-display text-xl font-semibold">Vision</h3>
            <p class="mt-3 text-ink/75 text-sm leading-relaxed">A self-sustaining community asset base — land, property, and enterprise — owned and governed transparently by the members who built it.</p>
          </div>
          <div class="reveal grain-card bg-paper rounded-md p-7 border border-teal/10" data-reveal>
            <div class="w-11 h-11 rounded-full bg-teal-deep flex items-center justify-center mb-5">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v18M5 4h11l-2 3 2 3H5"/></svg>
            </div>
            <h3 class="font-display text-xl font-semibold">Objectives</h3>
            <p class="mt-3 text-ink/75 text-sm leading-relaxed">Grow monthly savings steadily, invest with care and consensus, keep full financial records open to members, and reinvest returns into shared community projects.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ TEAM ============ -->
<section id="team" class="relative">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;03 — TEAM</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Governance</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Members &amp; Committee</h2>
          <p class="mt-3 text-ink/70 max-w-xl">The association is run by an elected executive committee, accountable to the general body of members every term.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-10">
          <!-- ID-card style member entries -->
          <div class="reveal relative bg-paper border-2 border-dashed border-teal/25 rounded-md p-5" data-reveal>
            <div class="w-14 h-14 rounded-full bg-teal-deep/10 border border-teal/20 flex items-center justify-center font-display text-teal text-lg">RH</div>
            <p class="font-display font-semibold mt-4">Rafiqul Haque</p>
            <p class="text-xs font-mono text-gold mt-0.5 uppercase tracking-wide">President</p>
            <p class="text-xs text-ink/60 mt-2">Member since 2019</p>
          </div>
          <div class="reveal relative bg-paper border-2 border-dashed border-teal/25 rounded-md p-5" data-reveal>
            <div class="w-14 h-14 rounded-full bg-teal-deep/10 border border-teal/20 flex items-center justify-center font-display text-teal text-lg">SA</div>
            <p class="font-display font-semibold mt-4">Sultana Akter</p>
            <p class="text-xs font-mono text-gold mt-0.5 uppercase tracking-wide">General Secretary</p>
            <p class="text-xs text-ink/60 mt-2">Member since 2019</p>
          </div>
          <div class="reveal relative bg-paper border-2 border-dashed border-teal/25 rounded-md p-5" data-reveal>
            <div class="w-14 h-14 rounded-full bg-teal-deep/10 border border-teal/20 flex items-center justify-center font-display text-teal text-lg">MI</div>
            <p class="font-display font-semibold mt-4">Mahmudul Islam</p>
            <p class="text-xs font-mono text-gold mt-0.5 uppercase tracking-wide">Treasurer</p>
            <p class="text-xs text-ink/60 mt-2">Member since 2020</p>
          </div>
          <div class="reveal relative bg-paper border-2 border-dashed border-teal/25 rounded-md p-5" data-reveal>
            <div class="w-14 h-14 rounded-full bg-teal-deep/10 border border-teal/20 flex items-center justify-center font-display text-teal text-lg">NJ</div>
            <p class="font-display font-semibold mt-4">Nusrat Jahan</p>
            <p class="text-xs font-mono text-gold mt-0.5 uppercase tracking-wide">Investment Lead</p>
            <p class="text-xs text-ink/60 mt-2">Member since 2021</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ MEMBERSHIP ============ -->
<section id="membership" class="bg-teal-deep text-paper">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-gold/60 tracking-widest">ENTRY&nbsp;04 — MEMBERSHIP</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-gold-light">Join us</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">How Membership Works</h2>
          <p class="mt-3 text-paper/70 max-w-xl">Four simple steps, and your name goes into the ledger.</p>
        </div>

        <div class="grid md:grid-cols-4 gap-6 mt-12">
          <div class="reveal" data-reveal>
            <p class="font-mono text-gold text-sm">01</p>
            <h3 class="font-display text-lg font-semibold mt-2">Apply</h3>
            <p class="text-paper/70 text-sm mt-2 leading-relaxed">Submit the membership form with your basic and NID information.</p>
          </div>
          <div class="reveal" data-reveal>
            <p class="font-mono text-gold text-sm">02</p>
            <h3 class="font-display text-lg font-semibold mt-2">Committee Review</h3>
            <p class="text-paper/70 text-sm mt-2 leading-relaxed">The executive committee reviews and approves new membership applications.</p>
          </div>
          <div class="reveal" data-reveal>
            <p class="font-mono text-gold text-sm">03</p>
            <h3 class="font-display text-lg font-semibold mt-2">Open Your Passbook</h3>
            <p class="text-paper/70 text-sm mt-2 leading-relaxed">Pay the one-time entrance fee and receive your member account &amp; passbook.</p>
          </div>
          <div class="reveal" data-reveal>
            <p class="font-mono text-gold text-sm">04</p>
            <h3 class="font-display text-lg font-semibold mt-2">Start Saving</h3>
            <p class="text-paper/70 text-sm mt-2 leading-relaxed">Begin your fixed monthly contribution and join the next member vote.</p>
          </div>
        </div>

        <div class="mt-14 flex flex-wrap gap-4">
          <a href="#contact" class="focus-ring inline-flex items-center gap-2 bg-gold text-teal-deep font-semibold px-6 py-3.5 rounded-full hover:bg-gold-light transition-colors">Request an Application</a>
          <a href="#faq" class="focus-ring inline-flex items-center gap-2 border border-paper/30 px-6 py-3.5 rounded-full hover:border-gold hover:text-gold transition-colors font-medium">Membership FAQ</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ SAVINGS ============ -->
<section id="savings" class="relative">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;05 — SAVINGS</span>
      </div>
      <div class="lg:col-span-11 grid lg:grid-cols-5 gap-10">
        <div class="lg:col-span-2">
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Monthly contribution</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Small, Steady, Together</h2>
          <p class="mt-4 text-ink/75 leading-relaxed">Every member commits to a fixed monthly savings amount, tiered by membership category. Contributions are collected by the Teller on a set schedule each month and recorded against your member account the same day.</p>
          <p class="mt-4 text-ink/75 leading-relaxed text-sm">Payments received after the due date carry a small, published late fee — the same rule for every member, without exception.</p>
        </div>

        <div class="lg:col-span-3 reveal bg-paper border border-teal/15 rounded-md overflow-hidden grain-card" data-reveal>
          <div class="bg-teal-deep text-paper px-6 py-4 flex items-center justify-between">
            <span class="font-display font-semibold">Contribution Tiers</span>
            <span class="font-mono text-xs text-gold-light">FY 2026</span>
          </div>
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-ink/50 uppercase text-xs tracking-wider border-b border-teal/10">
                <th class="px-6 py-3 font-medium">Tier</th>
                <th class="px-6 py-3 font-medium">Monthly</th>
                <th class="px-6 py-3 font-medium">Due Date</th>
              </tr>
            </thead>
            <tbody class="font-mono">
              <tr class="border-b border-teal/10"><td class="px-6 py-3.5">General Member</td><td class="px-6 py-3.5">৳ 1,000</td><td class="px-6 py-3.5">5th</td></tr>
              <tr class="border-b border-teal/10"><td class="px-6 py-3.5">Standard Member</td><td class="px-6 py-3.5">৳ 2,000</td><td class="px-6 py-3.5">5th</td></tr>
              <tr class="border-b border-teal/10"><td class="px-6 py-3.5">Founding Member</td><td class="px-6 py-3.5">৳ 5,000</td><td class="px-6 py-3.5">5th</td></tr>
              <tr><td class="px-6 py-3.5">Honorary Member</td><td class="px-6 py-3.5">Voluntary</td><td class="px-6 py-3.5">—</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ INVESTMENTS ============ -->
<section id="investments" class="bg-paperdark/60 border-y border-teal/10">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;06 — INVESTMENTS</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Where savings go to work</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Investment Activities</h2>
          <p class="mt-3 text-ink/70 max-w-xl">Every investment is proposed, discussed, and approved by members before a single taka moves.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mt-10">
          <div class="reveal grain-card bg-paper rounded-md p-6 border border-teal/10" data-reveal>
            <p class="font-mono text-xs text-gold uppercase tracking-wider">Category</p>
            <h3 class="font-display text-lg font-semibold mt-1">Land &amp; Property</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">Long-term land purchases in growth areas, held collectively and re-valued yearly.</p>
            <div class="mt-4 h-1.5 bg-teal/10 rounded-full overflow-hidden"><div class="h-full bg-teal w-[62%]"></div></div>
            <p class="text-xs font-mono text-ink/50 mt-1.5">62% of investment fund</p>
          </div>
          <div class="reveal grain-card bg-paper rounded-md p-6 border border-teal/10" data-reveal>
            <p class="font-mono text-xs text-gold uppercase tracking-wider">Category</p>
            <h3 class="font-display text-lg font-semibold mt-1">Small Enterprise</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">Modest working-capital loans and shared micro-business ventures within the member community.</p>
            <div class="mt-4 h-1.5 bg-teal/10 rounded-full overflow-hidden"><div class="h-full bg-teal w-[24%]"></div></div>
            <p class="text-xs font-mono text-ink/50 mt-1.5">24% of investment fund</p>
          </div>
          <div class="reveal grain-card bg-paper rounded-md p-6 border border-teal/10" data-reveal>
            <p class="font-mono text-xs text-gold uppercase tracking-wider">Category</p>
            <h3 class="font-display text-lg font-semibold mt-1">Fixed Deposits</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">A conservative reserve kept liquid for emergencies and short-notice member needs.</p>
            <div class="mt-4 h-1.5 bg-teal/10 rounded-full overflow-hidden"><div class="h-full bg-teal w-[14%]"></div></div>
            <p class="text-xs font-mono text-ink/50 mt-1.5">14% of investment fund</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ LAND ============ -->
<section id="land" class="relative">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;07 — LAND</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Land investment</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Land We Hold, Together</h2>
          <p class="mt-3 text-ink/70 max-w-xl">Every land purchase is registered in the member ledger with full documentation — deed, khatian, and mutation on file.</p>
        </div>

        <div class="mt-10 reveal relative bg-paper border border-teal/15 rounded-md grain-card overflow-hidden" data-reveal>
          <div class="grid md:grid-cols-5">
            <div class="md:col-span-2 bg-teal-deep text-paper p-8 flex flex-col justify-between">
              <div>
                <span class="inline-block text-xs font-mono tracking-wider uppercase bg-gold text-teal-deep px-2.5 py-1 rounded-full">Public · Verified</span>
                <h3 class="font-display text-2xl font-semibold mt-4">Dreamers Green City</h3>
                <p class="text-paper/70 text-sm mt-2">Savar, Dhaka District</p>
              </div>
              <p class="text-xs text-paper/50 mt-8 font-mono">DAG: 214–221 · MOUZA: Ashulia</p>
            </div>
            <div class="md:col-span-3 p-8 grid grid-cols-2 gap-6">
              <div>
                <p class="text-xs uppercase tracking-wider text-ink/50 font-mono">Land Size</p>
                <p class="font-display text-xl font-semibold mt-1">3.2 Bigha</p>
              </div>
              <div>
                <p class="text-xs uppercase tracking-wider text-ink/50 font-mono">Purchase Year</p>
                <p class="font-display text-xl font-semibold mt-1">2023</p>
              </div>
              <div>
                <p class="text-xs uppercase tracking-wider text-ink/50 font-mono">Status</p>
                <p class="font-display text-xl font-semibold mt-1 text-teal">Registered</p>
              </div>
              <div>
                <p class="text-xs uppercase tracking-wider text-ink/50 font-mono">Purpose</p>
                <p class="font-display text-xl font-semibold mt-1">Residential Plotting</p>
              </div>
            </div>
          </div>
        </div>
        <p class="text-xs text-ink/50 mt-3 font-mono">Only land marked "Show on Website" by the committee appears here. Full documents are available to members in the dashboard.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ PROJECTS ============ -->
<section id="projects" class="bg-paperdark/60 border-y border-teal/10">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;08 — PROJECTS</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal flex flex-wrap items-end justify-between gap-4" data-reveal>
          <div>
            <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Building forward</p>
            <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Projects</h2>
          </div>
          <div class="flex bg-paper border border-teal/15 rounded-full p-1">
            <button data-tab="current" class="proj-tab px-4 py-2 text-sm font-medium rounded-full bg-teal-deep text-paper transition-colors">Current</button>
            <button data-tab="future" class="proj-tab px-4 py-2 text-sm font-medium rounded-full text-ink/70 transition-colors">Future</button>
          </div>
        </div>

        <div id="proj-current" class="mt-10 grid md:grid-cols-3 gap-6">
          <div class="reveal grain-card bg-paper rounded-md p-6 border border-teal/10" data-reveal>
            <span class="text-xs font-mono uppercase tracking-wider text-teal bg-teal/10 px-2 py-1 rounded-full">Active</span>
            <h3 class="font-display text-lg font-semibold mt-3">Green City Plot Development</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">Boundary wall, road access, and utility groundwork for the Savar land project.</p>
            <p class="text-xs font-mono text-ink/50 mt-4">Started Jan 2025</p>
          </div>
          <div class="reveal grain-card bg-paper rounded-md p-6 border border-teal/10" data-reveal>
            <span class="text-xs font-mono uppercase tracking-wider text-teal bg-teal/10 px-2 py-1 rounded-full">Active</span>
            <h3 class="font-display text-lg font-semibold mt-3">Member Micro-Loan Fund</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">Small working-capital loans for members starting a home-based business.</p>
            <p class="text-xs font-mono text-ink/50 mt-4">Started Jun 2024</p>
          </div>
          <div class="reveal grain-card bg-paper rounded-md p-6 border border-teal/10" data-reveal>
            <span class="text-xs font-mono uppercase tracking-wider text-teal bg-teal/10 px-2 py-1 rounded-full">Active</span>
            <h3 class="font-display text-lg font-semibold mt-3">Digital Passbook Rollout</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">Moving every member's paper passbook onto the new online member dashboard.</p>
            <p class="text-xs font-mono text-ink/50 mt-4">Started Mar 2026</p>
          </div>
        </div>

        <div id="proj-future" class="mt-10 hidden grid md:grid-cols-3 gap-6">
          <div class="grain-card bg-paper rounded-md p-6 border border-teal/10">
            <span class="text-xs font-mono uppercase tracking-wider text-gold bg-gold/15 px-2 py-1 rounded-full">Planning</span>
            <h3 class="font-display text-lg font-semibold mt-3">Community Hall</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">A shared hall on association land for member meetings and events.</p>
            <p class="text-xs font-mono text-ink/50 mt-4">Proposed 2027</p>
          </div>
          <div class="grain-card bg-paper rounded-md p-6 border border-teal/10">
            <span class="text-xs font-mono uppercase tracking-wider text-gold bg-gold/15 px-2 py-1 rounded-full">Proposed</span>
            <h3 class="font-display text-lg font-semibold mt-3">Second Land Parcel</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">Evaluating a second land purchase near Gazipur, pending member vote.</p>
            <p class="text-xs font-mono text-ink/50 mt-4">Under Review</p>
          </div>
          <div class="grain-card bg-paper rounded-md p-6 border border-teal/10">
            <span class="text-xs font-mono uppercase tracking-wider text-gold bg-gold/15 px-2 py-1 rounded-full">Idea Stage</span>
            <h3 class="font-display text-lg font-semibold mt-3">Education Stipend</h3>
            <p class="text-sm text-ink/70 mt-2 leading-relaxed">A small annual fund for members' children pursuing higher education.</p>
            <p class="text-xs font-mono text-ink/50 mt-4">Idea Stage</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ TRANSPARENCY ============ -->
<section id="transparency" class="relative">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;09 — TRANSPARENCY</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Open books</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Financial Transparency</h2>
          <p class="mt-3 text-ink/70 max-w-xl">A running summary of the association's books, updated every quarter. Members see the full detail in their dashboard.</p>
        </div>

        <div class="mt-10 reveal grain-card bg-paper border border-teal/15 rounded-md overflow-hidden" data-reveal>
          <div class="bg-teal-deep text-paper px-6 py-4 flex flex-wrap items-center justify-between gap-2">
            <span class="font-display font-semibold">Q2 2026 Summary</span>
            <span class="font-mono text-xs text-gold-light">Apr – Jun 2026</span>
          </div>
          <div class="grid sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-teal/10">
            <div class="p-6">
              <p class="text-xs uppercase tracking-wider text-ink/50 font-mono">Total Income</p>
              <p class="font-mono text-2xl font-semibold text-teal mt-1">৳ 6,84,000</p>
              <p class="text-xs text-ink/50 mt-1">Contributions + returns</p>
            </div>
            <div class="p-6">
              <p class="text-xs uppercase tracking-wider text-ink/50 font-mono">Total Expense</p>
              <p class="font-mono text-2xl font-semibold text-stamp mt-1">৳ 2,10,500</p>
              <p class="text-xs text-ink/50 mt-1">Projects + operations</p>
            </div>
            <div class="p-6">
              <p class="text-xs uppercase tracking-wider text-ink/50 font-mono">Net Balance</p>
              <p class="font-mono text-2xl font-semibold mt-1">৳ 4,73,500</p>
              <p class="text-xs text-ink/50 mt-1">Carried forward</p>
            </div>
          </div>
        </div>
        <a href="#" class="focus-ring inline-flex items-center gap-2 mt-6 text-teal font-medium text-sm hover:text-teal-deep border-b border-teal/30 hover:border-teal-deep transition-colors">
          View full quarterly report (member login required)
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============ RULES & POLICIES ============ -->
<section id="rules" class="bg-paperdark/60 border-y border-teal/10">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;10 — RULES</span>
      </div>
      <div class="lg:col-span-11 lg:max-w-3xl">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">The fine print, in plain words</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Association Rules &amp; Policies</h2>
        </div>

        <div id="rulesAccordion" class="mt-10 divide-y divide-teal/15 border-y border-teal/15">
          <div class="rule-item">
            <button class="rule-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="flex items-baseline gap-4"><span class="font-mono text-gold text-sm">01</span><span class="font-display font-semibold">Monthly contribution is mandatory</span></span>
              <svg class="rule-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="rule-panel hidden pb-5 pl-9 pr-8 text-ink/70 text-sm leading-relaxed">Every member must pay their tier's fixed monthly amount by the 5th. Two consecutive missed months trigger a committee review of the account.</div>
          </div>
          <div class="rule-item">
            <button class="rule-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="flex items-baseline gap-4"><span class="font-mono text-gold text-sm">02</span><span class="font-display font-semibold">Investments require a member vote</span></span>
              <svg class="rule-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="rule-panel hidden pb-5 pl-9 pr-8 text-ink/70 text-sm leading-relaxed">Any investment or land purchase above the published threshold must be proposed, discussed at a general meeting, and approved by majority vote before funds are committed.</div>
          </div>
          <div class="rule-item">
            <button class="rule-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="flex items-baseline gap-4"><span class="font-mono text-gold text-sm">03</span><span class="font-display font-semibold">Early withdrawal policy</span></span>
              <svg class="rule-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="rule-panel hidden pb-5 pl-9 pr-8 text-ink/70 text-sm leading-relaxed">Members leaving before a minimum three-year term receive their principal savings back; accrued investment returns are settled per the association's exit schedule.</div>
          </div>
          <div class="rule-item">
            <button class="rule-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="flex items-baseline gap-4"><span class="font-mono text-gold text-sm">04</span><span class="font-display font-semibold">Voting eligibility</span></span>
              <svg class="rule-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="rule-panel hidden pb-5 pl-9 pr-8 text-ink/70 text-sm leading-relaxed">Members become eligible to vote on association decisions after six months of continuous, on-time contribution.</div>
          </div>
          <div class="rule-item">
            <button class="rule-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="flex items-baseline gap-4"><span class="font-mono text-gold text-sm">05</span><span class="font-display font-semibold">Records &amp; audits</span></span>
              <svg class="rule-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="rule-panel hidden pb-5 pl-9 pr-8 text-ink/70 text-sm leading-relaxed">Full financial records are maintained continuously and reviewed by an independent audit committee once a year, with findings shared at the annual general meeting.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ NOTICES ============ -->
<section id="notices" class="relative overflow-hidden">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 pt-20 sm:pt-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;11 — NOTICES</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Pinned to the board</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Notices &amp; Announcements</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-10 border-y border-teal/15 bg-paperdark/50 py-8">
    <div class="overflow-hidden">
      <div class="marquee-track flex gap-6 w-max px-6">
        <!-- duplicated set for seamless loop -->
        <div class="notice-slip flex-shrink-0 w-72 bg-paper border border-teal/15 rounded-md p-5 relative grain-card">
          <span class="absolute -top-3 -right-3 bg-stamp text-paper text-[10px] font-mono uppercase tracking-wider px-2 py-1 rotate-6 rounded-full shadow">Urgent</span>
          <p class="font-mono text-xs text-ink/50">Aug 12, 2026</p>
          <p class="font-display font-semibold mt-2">Monthly savings due by 5th</p>
          <p class="text-sm text-ink/70 mt-1.5">Please clear August contributions before the late-fee window opens.</p>
        </div>
        <div class="notice-slip flex-shrink-0 w-72 bg-paper border border-teal/15 rounded-md p-5 relative grain-card">
          <span class="absolute -top-3 -right-3 bg-gold text-teal-deep text-[10px] font-mono uppercase tracking-wider px-2 py-1 -rotate-3 rounded-full shadow">Notice</span>
          <p class="font-mono text-xs text-ink/50">Aug 05, 2026</p>
          <p class="font-display font-semibold mt-2">AGM scheduled for September</p>
          <p class="text-sm text-ink/70 mt-1.5">Annual General Meeting date and agenda to be shared in the member portal.</p>
        </div>
        <div class="notice-slip flex-shrink-0 w-72 bg-paper border border-teal/15 rounded-md p-5 relative grain-card">
          <span class="absolute -top-3 -right-3 bg-teal text-paper text-[10px] font-mono uppercase tracking-wider px-2 py-1 rotate-3 rounded-full shadow">Update</span>
          <p class="font-mono text-xs text-ink/50">Jul 28, 2026</p>
          <p class="font-display font-semibold mt-2">New land proposal open for review</p>
          <p class="text-sm text-ink/70 mt-1.5">Details of the Gazipur parcel are posted for member comment before voting.</p>
        </div>
        <div class="notice-slip flex-shrink-0 w-72 bg-paper border border-teal/15 rounded-md p-5 relative grain-card">
          <span class="absolute -top-3 -right-3 bg-stamp text-paper text-[10px] font-mono uppercase tracking-wider px-2 py-1 rotate-6 rounded-full shadow">Urgent</span>
          <p class="font-mono text-xs text-ink/50">Aug 12, 2026</p>
          <p class="font-display font-semibold mt-2">Monthly savings due by 5th</p>
          <p class="text-sm text-ink/70 mt-1.5">Please clear August contributions before the late-fee window opens.</p>
        </div>
        <div class="notice-slip flex-shrink-0 w-72 bg-paper border border-teal/15 rounded-md p-5 relative grain-card">
          <span class="absolute -top-3 -right-3 bg-gold text-teal-deep text-[10px] font-mono uppercase tracking-wider px-2 py-1 -rotate-3 rounded-full shadow">Notice</span>
          <p class="font-mono text-xs text-ink/50">Aug 05, 2026</p>
          <p class="font-display font-semibold mt-2">AGM scheduled for September</p>
          <p class="text-sm text-ink/70 mt-1.5">Annual General Meeting date and agenda to be shared in the member portal.</p>
        </div>
        <div class="notice-slip flex-shrink-0 w-72 bg-paper border border-teal/15 rounded-md p-5 relative grain-card">
          <span class="absolute -top-3 -right-3 bg-teal text-paper text-[10px] font-mono uppercase tracking-wider px-2 py-1 rotate-3 rounded-full shadow">Update</span>
          <p class="font-mono text-xs text-ink/50">Jul 28, 2026</p>
          <p class="font-display font-semibold mt-2">New land proposal open for review</p>
          <p class="text-sm text-ink/70 mt-1.5">Details of the Gazipur parcel are posted for member comment before voting.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ NEWS & EVENTS ============ -->
<section id="news" class="relative">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;12 — NEWS</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">What's happening</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">News &amp; Events</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mt-10">
          <article class="reveal grain-card bg-paper border border-teal/10 rounded-md overflow-hidden" data-reveal>
            <div class="flex">
              <div class="w-16 flex-shrink-0 bg-teal-deep text-paper flex flex-col items-center justify-center py-4">
                <span class="font-display text-xl font-semibold">14</span>
                <span class="text-[10px] uppercase tracking-wider text-gold-light">Sep</span>
              </div>
              <div class="p-5">
                <h3 class="font-display font-semibold leading-snug">Annual General Meeting 2026</h3>
                <p class="text-sm text-ink/70 mt-2">All members invited — budget review and committee elections.</p>
              </div>
            </div>
          </article>
          <article class="reveal grain-card bg-paper border border-teal/10 rounded-md overflow-hidden" data-reveal>
            <div class="flex">
              <div class="w-16 flex-shrink-0 bg-teal-deep text-paper flex flex-col items-center justify-center py-4">
                <span class="font-display text-xl font-semibold">02</span>
                <span class="text-[10px] uppercase tracking-wider text-gold-light">Aug</span>
              </div>
              <div class="p-5">
                <h3 class="font-display font-semibold leading-snug">Green City site visit</h3>
                <p class="text-sm text-ink/70 mt-2">A guided visit for members to the Savar land project boundary works.</p>
              </div>
            </div>
          </article>
          <article class="reveal grain-card bg-paper border border-teal/10 rounded-md overflow-hidden" data-reveal>
            <div class="flex">
              <div class="w-16 flex-shrink-0 bg-teal-deep text-paper flex flex-col items-center justify-center py-4">
                <span class="font-display text-xl font-semibold">19</span>
                <span class="text-[10px] uppercase tracking-wider text-gold-light">Jun</span>
              </div>
              <div class="p-5">
                <h3 class="font-display font-semibold leading-snug">Digital dashboard launched</h3>
                <p class="text-sm text-ink/70 mt-2">Members can now check their passbook balance online, from any phone.</p>
              </div>
            </div>
          </article>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ GALLERY ============ -->
<section id="gallery" class="bg-paperdark/60 border-y border-teal/10">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;13 — GALLERY</span>
      </div>
      <div class="lg:col-span-11">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Moments</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Gallery</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-10 mt-12">
          <button class="gallery-item reveal relative taped bg-paper p-2 pb-6 rotate-[-2deg] hover:rotate-0 transition-transform focus-ring" data-reveal data-caption="Member meeting, 2025">
            <div class="aspect-square bg-teal/10 flex items-center justify-center text-teal/40">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="1"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 15l-5-5-4 4-2-2-5 5"/></svg>
            </div>
            <p class="text-xs font-mono text-ink/60 mt-2 text-center">Member Meeting</p>
          </button>
          <button class="gallery-item reveal relative taped bg-paper p-2 pb-6 rotate-[2deg] hover:rotate-0 transition-transform focus-ring" data-reveal data-caption="Green City land visit">
            <div class="aspect-square bg-teal/10 flex items-center justify-center text-teal/40">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="1"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 15l-5-5-4 4-2-2-5 5"/></svg>
            </div>
            <p class="text-xs font-mono text-ink/60 mt-2 text-center">Land Site Visit</p>
          </button>
          <button class="gallery-item reveal relative taped bg-paper p-2 pb-6 rotate-[-1.5deg] hover:rotate-0 transition-transform focus-ring" data-reveal data-caption="Passbook distribution day">
            <div class="aspect-square bg-teal/10 flex items-center justify-center text-teal/40">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="1"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 15l-5-5-4 4-2-2-5 5"/></svg>
            </div>
            <p class="text-xs font-mono text-ink/60 mt-2 text-center">Passbook Day</p>
          </button>
          <button class="gallery-item reveal relative taped bg-paper p-2 pb-6 rotate-[2.5deg] hover:rotate-0 transition-transform focus-ring" data-reveal data-caption="Annual General Meeting 2025">
            <div class="aspect-square bg-teal/10 flex items-center justify-center text-teal/40">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="1"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 15l-5-5-4 4-2-2-5 5"/></svg>
            </div>
            <p class="text-xs font-mono text-ink/60 mt-2 text-center">AGM 2025</p>
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- gallery lightbox -->
<div id="lightbox" class="hidden fixed inset-0 z-[60] bg-ink/85 items-center justify-center p-6">
  <button id="lightboxClose" aria-label="Close" class="absolute top-6 right-6 text-paper focus-ring rounded w-10 h-10 flex items-center justify-center border border-paper/30">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
  </button>
  <div class="bg-paper p-3 pb-8 max-w-sm w-full">
    <div class="aspect-square bg-teal/10 flex items-center justify-center text-teal/40">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="1"/><circle cx="8.5" cy="10" r="1.5"/><path d="M21 15l-5-5-4 4-2-2-5 5"/></svg>
    </div>
    <p id="lightboxCaption" class="text-center font-mono text-sm text-ink/70 mt-3"></p>
  </div>
</div>

<!-- ============ CONTACT ============ -->
<section id="contact" class="bg-teal-deep text-paper">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-gold/60 tracking-widest">ENTRY&nbsp;14 — CONTACT</span>
      </div>
      <div class="lg:col-span-11 grid lg:grid-cols-2 gap-12">
        <div>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-gold-light">Get in touch</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Contact the Committee</h2>
          <p class="mt-4 text-paper/70 leading-relaxed max-w-md">Questions about membership, savings, or an existing account? Reach us directly, or drop by the office on collection day.</p>

          <div class="mt-8 space-y-5">
            <div class="flex items-start gap-4">
              <div class="w-9 h-9 rounded-full border border-gold/40 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
              </div>
              <p class="text-sm text-paper/80">Dreamers Association Office,<br>Road 7, Block C, Savar, Dhaka</p>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-9 h-9 rounded-full border border-gold/40 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              </div>
              <p class="text-sm text-paper/80">+880 1XXX-XXXXXX</p>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-9 h-9 rounded-full border border-gold/40 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              </div>
              <p class="text-sm text-paper/80">hello@dreamersassociation.org</p>
            </div>
          </div>
        </div>

        <form class="reveal bg-paper text-ink rounded-md p-6 sm:p-8 grain-card" data-reveal onsubmit="event.preventDefault(); this.reset(); document.getElementById('formSent').classList.remove('hidden');">
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-mono uppercase tracking-wider text-ink/60" for="cf-name">Name</label>
              <input id="cf-name" required type="text" class="mt-1.5 w-full bg-paperdark/40 border border-teal/20 rounded-md px-3.5 py-2.5 text-sm focus:outline-none focus:border-gold focus-ring">
            </div>
            <div>
              <label class="text-xs font-mono uppercase tracking-wider text-ink/60" for="cf-phone">Phone</label>
              <input id="cf-phone" type="text" class="mt-1.5 w-full bg-paperdark/40 border border-teal/20 rounded-md px-3.5 py-2.5 text-sm focus:outline-none focus:border-gold focus-ring">
            </div>
          </div>
          <div class="mt-4">
            <label class="text-xs font-mono uppercase tracking-wider text-ink/60" for="cf-email">Email</label>
            <input id="cf-email" required type="email" class="mt-1.5 w-full bg-paperdark/40 border border-teal/20 rounded-md px-3.5 py-2.5 text-sm focus:outline-none focus:border-gold focus-ring">
          </div>
          <div class="mt-4">
            <label class="text-xs font-mono uppercase tracking-wider text-ink/60" for="cf-msg">Message</label>
            <textarea id="cf-msg" required rows="4" class="mt-1.5 w-full bg-paperdark/40 border border-teal/20 rounded-md px-3.5 py-2.5 text-sm focus:outline-none focus:border-gold focus-ring"></textarea>
          </div>
          <button type="submit" class="focus-ring mt-5 w-full bg-teal-deep text-paper font-semibold py-3 rounded-full hover:bg-teal transition-colors">Send Message</button>
          <p id="formSent" class="hidden mt-3 text-sm text-teal font-medium">✓ Thank you — the committee will get back to you soon.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section id="faq" class="relative">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 py-20 sm:py-28">
    <div class="grid lg:grid-cols-12 gap-10">
      <div class="lg:col-span-1 hidden lg:flex justify-center">
        <span class="tab-num font-mono text-sm text-teal/60 tracking-widest">ENTRY&nbsp;15 — FAQ</span>
      </div>
      <div class="lg:col-span-11 lg:max-w-3xl">
        <div class="reveal" data-reveal>
          <p class="font-mono text-xs uppercase tracking-[0.25em] text-teal">Common questions</p>
          <h2 class="font-display text-3xl sm:text-4xl font-semibold mt-3">Frequently Asked Questions</h2>
        </div>

        <div id="faqAccordion" class="mt-10 divide-y divide-teal/15 border-y border-teal/15">
          <div class="faq-item">
            <button class="faq-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="font-display font-semibold">Who can become a member?</span>
              <svg class="faq-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-panel hidden pb-5 pr-8 text-ink/70 text-sm leading-relaxed">Any adult introduced by an existing member and approved by the committee may apply. Priority is given to applicants who can commit to consistent monthly savings.</div>
          </div>
          <div class="faq-item">
            <button class="faq-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="font-display font-semibold">Can I change my contribution tier later?</span>
              <svg class="faq-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-panel hidden pb-5 pr-8 text-ink/70 text-sm leading-relaxed">Yes. Members can request a tier change once per year, effective from the next contribution cycle, subject to committee confirmation.</div>
          </div>
          <div class="faq-item">
            <button class="faq-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="font-display font-semibold">How do I check my balance?</span>
              <svg class="faq-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-panel hidden pb-5 pr-8 text-ink/70 text-sm leading-relaxed">Log in to the member dashboard using the "Member Login" button at the top of this page to see your contribution history, balance, and investment share at any time.</div>
          </div>
          <div class="faq-item">
            <button class="faq-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="font-display font-semibold">How are investment decisions made?</span>
              <svg class="faq-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-panel hidden pb-5 pr-8 text-ink/70 text-sm leading-relaxed">The investment committee proposes options with full cost and risk detail; eligible members vote at a general meeting, and only majority-approved investments proceed.</div>
          </div>
          <div class="faq-item">
            <button class="faq-btn w-full flex items-center justify-between text-left py-5 focus-ring rounded" aria-expanded="false">
              <span class="font-display font-semibold">What happens if I miss a payment?</span>
              <svg class="faq-icon w-5 h-5 text-teal transition-transform flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-panel hidden pb-5 pr-8 text-ink/70 text-sm leading-relaxed">A late fee applies after the due date. If two consecutive months are missed, the committee reaches out directly to review the member's account status.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<!-- ============ FOOTER — the back cover ============ -->
<footer class="bg-teal-deep text-paper lg:pl-[10px]">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 pt-16 pb-8">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10">
      <div>
        <div class="flex items-center gap-3">
          <span class="w-9 h-9 rounded-full border-2 border-gold flex items-center justify-center font-display font-semibold text-gold text-lg">D</span>
          <span class="font-display text-lg">Dreamers <span class="text-gold">Association</span></span>
        </div>
        <p class="text-paper/60 text-sm mt-4 leading-relaxed">A member-owned savings, investment and land-development cooperative. Established 2019.</p>
      </div>
      <div>
        <p class="font-mono text-xs uppercase tracking-wider text-gold-light mb-4">Explore</p>
        <ul class="space-y-2.5 text-sm text-paper/70">
          <li><a href="#about" class="hover:text-gold transition-colors focus-ring rounded">About Us</a></li>
          <li><a href="#mission" class="hover:text-gold transition-colors focus-ring rounded">Mission &amp; Vision</a></li>
          <li><a href="#membership" class="hover:text-gold transition-colors focus-ring rounded">Membership</a></li>
          <li><a href="#projects" class="hover:text-gold transition-colors focus-ring rounded">Projects</a></li>
        </ul>
      </div>
      <div>
        <p class="font-mono text-xs uppercase tracking-wider text-gold-light mb-4">Resources</p>
        <ul class="space-y-2.5 text-sm text-paper/70">
          <li><a href="#transparency" class="hover:text-gold transition-colors focus-ring rounded">Financial Reports</a></li>
          <li><a href="#rules" class="hover:text-gold transition-colors focus-ring rounded">Rules &amp; Policies</a></li>
          <li><a href="#notices" class="hover:text-gold transition-colors focus-ring rounded">Notices</a></li>
          <li><a href="#faq" class="hover:text-gold transition-colors focus-ring rounded">FAQ</a></li>
        </ul>
      </div>
      <div>
        <p class="font-mono text-xs uppercase tracking-wider text-gold-light mb-4">Member Access</p>
        <p class="text-sm text-paper/70 leading-relaxed mb-4">Already a member? Sign in to your dashboard to view your passbook.</p>
        <a href="#" class="focus-ring inline-flex items-center gap-2 bg-gold text-teal-deep font-semibold text-sm px-5 py-2.5 rounded-full hover:bg-gold-light transition-colors">Member Login</a>
      </div>
    </div>

    <div class="mt-12 pt-6 border-t border-paper/15 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-xs text-paper/50 font-mono">© 2026 Dreamers Association. All rights reserved.</p>
      <div class="flex items-center gap-2">
        <span class="w-10 h-10 rounded-full border border-gold/50 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M14 9V6a2 2 0 012-2h1.5M14 9h4l-1 5h-3v7h-3v-7H8V9h3V6.5a3.5 3.5 0 013.5-3.5H17"/></svg>
        </span>
        <span class="text-xs text-paper/50 font-mono uppercase tracking-wider">Bangla · English</span>
      </div>
    </div>
  </div>
</footer>

<!-- back-to-top -->
<a href="#top" aria-label="Back to top" class="focus-ring fixed bottom-6 right-6 z-40 w-11 h-11 rounded-full bg-gold text-teal-deep flex items-center justify-center shadow-lg hover:bg-gold-light transition-colors">
  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
</a>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // ---------- mobile menu ----------
  var menuBtn = document.getElementById('menuBtn');
  var mobileNav = document.getElementById('mobileNav');
  var iconOpen = document.getElementById('menuIconOpen');
  var iconClose = document.getElementById('menuIconClose');

  menuBtn.addEventListener('click', function () {
    var isHidden = mobileNav.classList.contains('hidden');
    mobileNav.classList.toggle('hidden');
    iconOpen.classList.toggle('hidden');
    iconClose.classList.toggle('hidden');
    menuBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
  });
  document.querySelectorAll('.mobile-link').forEach(function (a) {
    a.addEventListener('click', function () {
      mobileNav.classList.add('hidden');
      iconOpen.classList.remove('hidden');
      iconClose.classList.add('hidden');
      menuBtn.setAttribute('aria-expanded', 'false');
    });
  });

  // ---------- scroll reveal ----------
  var revealEls = document.querySelectorAll('[data-reveal]');
  var revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('in');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });
  revealEls.forEach(function (el) {
    el.classList.add(el.closest('#home') ? 'stamp-pop' : 'reveal');
    revealObserver.observe(el);
  });

  // ---------- count up stats ----------
  var counters = document.querySelectorAll('.count-up');
  var counterObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      var target = parseInt(el.getAttribute('data-target'), 10);
      var prefix = el.getAttribute('data-prefix') || '';
      var suffix = el.getAttribute('data-suffix') || '';
      var duration = 1400;
      var start = null;

      function step(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        var value = Math.floor(eased * target);
        el.textContent = prefix + value + suffix;
        if (progress < 1) {
          requestAnimationFrame(step);
        } else {
          el.textContent = prefix + target + suffix;
        }
      }
      requestAnimationFrame(step);
      counterObserver.unobserve(el);
    });
  }, { threshold: 0.4 });
  counters.forEach(function (c) { counterObserver.observe(c); });

  // ---------- FAQ accordion ----------
  function setupAccordion(itemSelector, btnSelector, panelSelector, iconSelector) {
    document.querySelectorAll(itemSelector).forEach(function (item) {
      var btn = item.querySelector(btnSelector);
      var panel = item.querySelector(panelSelector);
      var icon = item.querySelector(iconSelector);
      btn.addEventListener('click', function () {
        var isOpen = !panel.classList.contains('hidden');
        panel.classList.toggle('hidden');
        icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      });
    });
  }
  setupAccordion('.faq-item', '.faq-btn', '.faq-panel', '.faq-icon');
  setupAccordion('.rule-item', '.rule-btn', '.rule-panel', '.rule-icon');

  // ---------- project tabs ----------
  var tabs = document.querySelectorAll('.proj-tab');
  var current = document.getElementById('proj-current');
  var future = document.getElementById('proj-future');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) {
        t.classList.remove('bg-teal-deep', 'text-paper');
        t.classList.add('text-ink/70');
      });
      tab.classList.add('bg-teal-deep', 'text-paper');
      tab.classList.remove('text-ink/70');
      if (tab.getAttribute('data-tab') === 'current') {
        current.classList.remove('hidden');
        future.classList.add('hidden');
      } else {
        future.classList.remove('hidden');
        current.classList.add('hidden');
      }
    });
  });

  // ---------- gallery lightbox ----------
  var lightbox = document.getElementById('lightbox');
  var lightboxCaption = document.getElementById('lightboxCaption');
  var lightboxClose = document.getElementById('lightboxClose');
  document.querySelectorAll('.gallery-item').forEach(function (item) {
    item.addEventListener('click', function () {
      lightboxCaption.textContent = item.getAttribute('data-caption') || '';
      lightbox.classList.remove('hidden');
      lightbox.classList.add('flex');
      document.body.classList.add('noscroll');
    });
  });
  function closeLightbox() {
    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
    document.body.classList.remove('noscroll');
  }
  lightboxClose.addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', function (e) { if (e.target === lightbox) closeLightbox(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLightbox(); });

  // ---------- scrollspy for desktop nav ----------
  var sections = ['about', 'membership', 'investments', 'land', 'projects', 'transparency', 'news', 'contact']
    .map(function (id) { return document.getElementById(id); })
    .filter(Boolean);
  var navLinks = document.querySelectorAll('.nav-link');
  var spyObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      var id = entry.target.getAttribute('id');
      var link = document.querySelector('.nav-link[href="#' + id + '"]');
      if (!link) return;
      if (entry.isIntersecting) {
        navLinks.forEach(function (l) { l.classList.remove('text-gold'); });
        link.classList.add('text-gold');
      }
    });
  }, { rootMargin: '-40% 0px -50% 0px' });
  sections.forEach(function (s) { spyObserver.observe(s); });

});
</script>

</body>
</html>
