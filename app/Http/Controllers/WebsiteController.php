<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSection;

class WebsiteController extends Controller
{
    public function index()
    {
        $sections = WebsiteSection::orderBy('sort_order')
            ->get()
            ->keyBy('section_key');

        return view('landing.index', compact('sections'));
    }

    public function about()
    {
        return view('landing.about');
    }

    public function activities()
    {
        return view('landing.activities');
    }

    public function transparency()
    {
        return view('landing.transparency');
    }

    public function faq()
    {
        return view('landing.faq');
    }
}
