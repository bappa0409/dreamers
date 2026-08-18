@extends('layouts.public')

@section('title', 'Dreamers Association')

@section('content')

<!-- =========================================================
     NAVBAR
========================================================= -->

<header
    class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl"
>
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">

        <a href="/" class="flex items-center gap-3">
            <div
                class="flex h-10 w-10 items-center justify-center rounded-md bg-white text-lg font-bold text-slate-950"
            >
                D
            </div>

            <div>
                <div class="font-bold text-white">
                    Dreamers
                </div>

                <div class="text-xs text-slate-400">
                    Association
                </div>
            </div>
        </a>


        <nav class="hidden items-center gap-8 md:flex">

            <a
                href="#home"
                class="text-sm text-slate-300 transition hover:text-white"
            >
                Home
            </a>

            <a
                href="#about"
                class="text-sm text-slate-300 transition hover:text-white"
            >
                About
            </a>

            <a
                href="#mission"
                class="text-sm text-slate-300 transition hover:text-white"
            >
                Mission
            </a>

            <a
                href="#projects"
                class="text-sm text-slate-300 transition hover:text-white"
            >
                Projects
            </a>

            <a
                href="#contact"
                class="text-sm text-slate-300 transition hover:text-white"
            >
                Contact
            </a>

        </nav>


        <a
            href="/login"
            class="rounded-md bg-white px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-slate-200"
        >
            Login
        </a>

    </div>
</header>


<main>

<!-- =========================================================
     HERO
========================================================= -->

<section
    id="home"
    class="relative flex min-h-screen items-center overflow-hidden bg-slate-950"
>

    <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(59,130,246,.25),transparent_35%),radial-gradient(circle_at_80%_80%,rgba(139,92,246,.20),transparent_35%)]"
    ></div>

    <div class="relative mx-auto w-full max-w-7xl px-6 pt-32 pb-20">

        <div class="grid items-center gap-16 lg:grid-cols-2">

            <div>

                <div
                    class="mb-6 inline-flex rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300"
                >
                    Dreamers Association
                </div>

                <h1
                    class="max-w-4xl text-5xl font-black leading-tight tracking-tight text-white sm:text-6xl lg:text-7xl"
                >
                    Build Dreams.
                    <span class="text-blue-400">
                        Create Future.
                    </span>
                </h1>

                <p
                    class="mt-7 max-w-2xl text-lg leading-8 text-slate-400"
                >
                    Together we can create opportunities, build meaningful
                    projects and make a positive impact through collaboration.
                </p>

                <div class="mt-9 flex flex-wrap gap-4">

                    <a
                        href="#about"
                        class="rounded-md bg-white px-6 py-3.5 font-semibold text-slate-950 transition hover:-translate-y-0.5 hover:bg-slate-200"
                    >
                        Explore More
                    </a>

                    <a
                        href="/login"
                        class="rounded-md border border-white/15 bg-white/5 px-6 py-3.5 font-semibold text-white transition hover:bg-white/10"
                    >
                        Member Login
                    </a>

                </div>

            </div>


            <div class="relative">

                <div
                    class="absolute -inset-10 rounded-full bg-blue-500/20 blur-3xl"
                ></div>

                <div
                    class="relative rounded-3xl border border-white/10 bg-white/5 p-3 shadow-2xl backdrop-blur"
                >

                    <div
                        class="flex aspect-square items-center justify-center rounded-md bg-gradient-to-br from-blue-500/30 to-purple-500/20"
                    >

                        <div class="text-center">

                            <div
                                class="mx-auto flex h-28 w-28 items-center justify-center rounded-3xl bg-white text-5xl font-black text-slate-950 shadow-2xl"
                            >
                                D
                            </div>

                            <h3
                                class="mt-6 text-2xl font-bold text-white"
                            >
                                Dreamers Association
                            </h3>

                            <p
                                class="mt-2 text-slate-400"
                            >
                                Together We Grow
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     ABOUT
========================================================= -->

<section
    id="about"
    class="bg-white py-24"
>

    <div class="mx-auto max-w-7xl px-6">

        <div class="grid gap-16 lg:grid-cols-2 lg:items-center">

            <div>

                <p
                    class="text-sm font-bold uppercase tracking-[0.25em] text-blue-600"
                >
                    About Us
                </p>

                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-slate-900 sm:text-5xl"
                >
                    A community built around dreams.
                </h2>

                <p
                    class="mt-6 text-lg leading-8 text-slate-600"
                >
                    Dreamers Association is a community focused on
                    collaboration, investment, development and creating
                    opportunities for its members.
                </p>

                <p
                    class="mt-4 leading-7 text-slate-500"
                >
                    We believe that when people work together with a shared
                    vision, they can create meaningful and sustainable
                    change.
                </p>

            </div>


            <div class="grid grid-cols-2 gap-5">

                <div
                    class="rounded-3xl bg-slate-950 p-7 text-white"
                >
                    <div class="text-4xl font-black">
                        500+
                    </div>

                    <div class="mt-2 text-slate-400">
                        Members
                    </div>
                </div>

                <div
                    class="rounded-3xl bg-slate-100 p-7"
                >
                    <div class="text-4xl font-black text-slate-900">
                        20+
                    </div>

                    <div class="mt-2 text-slate-500">
                        Projects
                    </div>
                </div>

                <div
                    class="rounded-3xl bg-blue-600 p-7 text-white"
                >
                    <div class="text-4xl font-black">
                        10+
                    </div>

                    <div class="mt-2 text-blue-100">
                        Investments
                    </div>
                </div>

                <div
                    class="rounded-3xl bg-slate-100 p-7"
                >
                    <div class="text-4xl font-black text-slate-900">
                        100%
                    </div>

                    <div class="mt-2 text-slate-500">
                        Together
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     MISSION & VISION
========================================================= -->

<section
    id="mission"
    class="bg-slate-50 py-24"
>

    <div class="mx-auto max-w-7xl px-6">

        <div class="mx-auto max-w-2xl text-center">

            <p
                class="text-sm font-bold uppercase tracking-[0.25em] text-blue-600"
            >
                Our Purpose
            </p>

            <h2
                class="mt-4 text-4xl font-black text-slate-900 sm:text-5xl"
            >
                Mission & Vision
            </h2>

        </div>


        <div class="mt-14 grid gap-7 md:grid-cols-2">

            <div
                class="rounded-3xl bg-white p-10  ring-1 ring-slate-200"
            >

                <div
                    class="flex h-14 w-14 items-center justify-center rounded-md bg-blue-100 text-2xl text-blue-600"
                >
                    M
                </div>

                <h3
                    class="mt-7 text-2xl font-bold text-slate-900"
                >
                    Our Mission
                </h3>

                <p
                    class="mt-4 leading-8 text-slate-600"
                >
                    To create opportunities through cooperation,
                    responsible investment and sustainable development.
                </p>

            </div>


            <div
                class="rounded-3xl bg-slate-950 p-10 text-white shadow-xl"
            >

                <div
                    class="flex h-14 w-14 items-center justify-center rounded-md bg-white/10 text-2xl"
                >
                    V
                </div>

                <h3
                    class="mt-7 text-2xl font-bold"
                >
                    Our Vision
                </h3>

                <p
                    class="mt-4 leading-8 text-slate-400"
                >
                    To build a strong and sustainable community where
                    everyone can grow together.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     PROJECTS
========================================================= -->

<section
    id="projects"
    class="bg-white py-24"
>

    <div class="mx-auto max-w-7xl px-6">

        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">

            <div>

                <p
                    class="text-sm font-bold uppercase tracking-[0.25em] text-blue-600"
                >
                    What We Do
                </p>

                <h2
                    class="mt-4 text-4xl font-black text-slate-900 sm:text-5xl"
                >
                    Our Projects
                </h2>

            </div>

            <a
                href="/login"
                class="font-semibold text-blue-600 hover:text-blue-700"
            >
                Explore Projects →
            </a>

        </div>


        <div class="mt-14 grid gap-6 md:grid-cols-3">

            <div
                class="group overflow-hidden rounded-3xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-xl"
            >

                <div
                    class="h-56 bg-gradient-to-br from-blue-500 to-indigo-600"
                ></div>

                <div class="p-7">

                    <h3
                        class="text-xl font-bold text-slate-900"
                    >
                        Community Development
                    </h3>

                    <p
                        class="mt-3 leading-7 text-slate-500"
                    >
                        Building meaningful projects that create long-term
                        value for our community.
                    </p>

                </div>

            </div>


            <div
                class="group overflow-hidden rounded-3xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-xl"
            >

                <div
                    class="h-56 bg-gradient-to-br from-emerald-500 to-teal-600"
                ></div>

                <div class="p-7">

                    <h3
                        class="text-xl font-bold text-slate-900"
                    >
                        Investment
                    </h3>

                    <p
                        class="mt-3 leading-7 text-slate-500"
                    >
                        Creating opportunities through responsible
                        collective investment.
                    </p>

                </div>

            </div>


            <div
                class="group overflow-hidden rounded-3xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-xl"
            >

                <div
                    class="h-56 bg-gradient-to-br from-purple-500 to-pink-600"
                ></div>

                <div class="p-7">

                    <h3
                        class="text-xl font-bold text-slate-900"
                    >
                        Land Development
                    </h3>

                    <p
                        class="mt-3 leading-7 text-slate-500"
                    >
                        Exploring sustainable land and property development
                        opportunities.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     CTA
========================================================= -->

<section class="bg-slate-950 py-24">

    <div class="mx-auto max-w-5xl px-6 text-center">

        <p
            class="text-sm font-bold uppercase tracking-[0.25em] text-blue-400"
        >
            Be Part Of The Journey
        </p>

        <h2
            class="mt-5 text-4xl font-black text-white sm:text-6xl"
        >
            Let's build the future together.
        </h2>

        <p
            class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-400"
        >
            Join Dreamers Association and become part of a community
            focused on growth, collaboration and opportunity.
        </p>

        <div class="mt-9">

            <a
                href="/login"
                class="inline-flex rounded-md bg-white px-7 py-4 font-bold text-slate-950 transition hover:bg-slate-200"
            >
                Join Now
            </a>

        </div>

    </div>

</section>


<!-- =========================================================
     CONTACT
========================================================= -->

<section
    id="contact"
    class="bg-white py-24"
>

    <div class="mx-auto max-w-7xl px-6">

        <div class="grid gap-14 lg:grid-cols-2">

            <div>

                <p
                    class="text-sm font-bold uppercase tracking-[0.25em] text-blue-600"
                >
                    Contact
                </p>

                <h2
                    class="mt-4 text-4xl font-black text-slate-900"
                >
                    Get in touch.
                </h2>

                <p
                    class="mt-6 max-w-xl text-lg leading-8 text-slate-600"
                >
                    Have a question or want to learn more about Dreamers
                    Association? We would love to hear from you.
                </p>

            </div>


            <div class="space-y-5">

                <div
                    class="rounded-md border border-slate-200 p-6"
                >
                    <div class="text-sm text-slate-500">
                        Email
                    </div>

                    <div class="mt-1 font-semibold text-slate-900">
                        info@dreamersassociation.com
                    </div>
                </div>


                <div
                    class="rounded-md border border-slate-200 p-6"
                >
                    <div class="text-sm text-slate-500">
                        Phone
                    </div>

                    <div class="mt-1 font-semibold text-slate-900">
                        +880 1XXX-XXXXXX
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>

</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="bg-slate-950 py-10">

    <div
        class="mx-auto flex max-w-7xl flex-col justify-between gap-5 px-6 sm:flex-row sm:items-center"
    >

        <div class="text-sm text-slate-400">
            © {{ date('Y') }} Dreamers Association. All rights reserved.
        </div>

        <div class="flex gap-6 text-sm text-slate-400">

            <a href="#about" class="hover:text-white">
                About
            </a>

            <a href="#projects" class="hover:text-white">
                Projects
            </a>

            <a href="#contact" class="hover:text-white">
                Contact
            </a>

            <a href="/login" class="hover:text-white">
                Login
            </a>

        </div>

    </div>

</footer>

@endsection