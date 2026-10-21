<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NewsletterSubscriber;
use App\Setting;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        // Hide the form and reject direct subscription requests when disabled.
        abort_if(Setting::get('lk_newsletter_enabled') === '0', 403, 'Newsletter subscriptions are disabled.');

        $request->validate([
            'newsletter_email' => 'required|email:rfc|max:254',
            'website' => 'nullable|max:0', // honeypot
        ]);

        $email = strtolower(trim($request->input('newsletter_email')));
        // Create once, preserving the original subscription date on repeat requests.
        NewsletterSubscriber::firstOrCreate(['email' => $email]);
        return redirect()->back()->with('newsletter_success', 'Thank you for subscribing!');
    }
}
