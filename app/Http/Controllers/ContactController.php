<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required|string|max:2000',
            'captchaId' => 'required',
            'validatedCaptcha' => 'required',
            'botCheck' => 'nullable|string|max:0',
        ]);

        // Bot honeypot check
        if (!empty($data['botCheck'])) {
            return redirect('/contact')->withErrors(['captcha' => 'Bot detected.']);
        }

        // Validate CAPTCHA server-side
        if ($data['captchaId'] !== $data['validatedCaptcha']) {
            return redirect('/contact')->withErrors(['captcha' => 'CAPTCHA failed. Please try again.']);
        }

        // Rename key to avoid collision
        $data['user_message'] = $data['message'];
        unset($data['message']);

        // Send email
        Mail::send('emails.contact', $data, function ($message) use ($data) {
            $message->to('ryandickinson54@gmail.com')
                ->subject('New Portfolio Contact Form Submission');
        });

        return redirect('/contact')->with('success', 'Thanks for your message!');
    }
}
