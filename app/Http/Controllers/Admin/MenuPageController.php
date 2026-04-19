<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class MenuPageController extends Controller
{
    public function userProfile(): View
    {
        return $this->page('User Profile', 'Manage the currently signed-in staff profile.');
    }

    public function messages(): View
    {
        return $this->page('Messages', 'View all incoming and internal messages here.');
    }

    public function bookingHistory(): View
    {
        return $this->page('Booking History', 'Review all recorded bookings and their activity.');
    }

    public function yetToBalance(): View
    {
        return $this->page('Yet to Balance', 'Track bookings that still have outstanding balances.');
    }

    public function bookNow(): View
    {
        return $this->page('Book Now', 'Create a new booking from the admin panel.');
    }

    public function services(): View
    {
        return $this->page('List All Services', 'Review every available service item.');
    }

    public function addService(): View
    {
        return $this->page('Add New Service', 'Create a new service entry for bookings.');
    }

    public function agreement(): View
    {
        return $this->page('Agreement', 'Manage the agreement content used across bookings.');
    }

    public function eventTypes(): View
    {
        return $this->page('Type of Event', 'Manage the event types available in the system.');
    }

    public function viewUsers(): View
    {
        return $this->page('View Users', 'Review user accounts and staff access.');
    }

    public function contactPage(): View
    {
        return $this->page('Contact Us', 'Manage the website contact page details.');
    }

    public function gallery(): View
    {
        return $this->page('Gallery', 'Manage website gallery items and images.');
    }

    public function smtp(): View
    {
        return $this->page('Add SMTP Details', 'Manage SMTP credentials used for outgoing mail.');
    }

    public function bookingSearch(): View
    {
        return $this->page('Booking Search', 'Search bookings quickly from one central screen.');
    }

    public function reports(): View
    {
        return $this->page('Report', 'Access reporting and summary outputs here.');
    }

    private function page(string $title, string $description): View
    {
        return view('pages.placeholder', [
            'title' => $title,
            'description' => $description,
        ]);
    }
}
