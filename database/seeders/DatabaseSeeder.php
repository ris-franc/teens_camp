<?php

namespace Database\Seeders;

use App\Models\AdoptATeenKitty;
use App\Models\AdoptATeenRequest;
use App\Models\CampaignProduct;
use App\Models\CampaignSale;
use App\Models\CampaignWeeklyBatch;
use App\Models\CampSeason;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\FormSubmissionValue;
use App\Models\Notification;
use App\Models\PackingList;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Core Staff & Test Users (Kenya Context)
        $admin = User::create([
            'name' => 'Pastor David Admin',
            'email' => 'admin@church.org',
            'pin' => Hash::make('1234'),
            'role' => 'admin',
            'phone' => '+254 722 100 200',
            'pin_reset_required' => false,
        ]);

        $pastor = User::create([
            'name' => 'Senior Pastor Michael Mwangi',
            'email' => 'pastor@church.org',
            'pin' => Hash::make('1234'),
            'role' => 'pastor',
            'phone' => '+254 722 300 400',
            'pin_reset_required' => false,
        ]);

        $registrationStaff = User::create([
            'name' => 'Rachel Wanjiku (Registration)',
            'email' => 'registration@church.org',
            'pin' => Hash::make('1234'),
            'role' => 'registration',
            'phone' => '+254 733 500 600',
            'pin_reset_required' => false,
        ]);

        $campaignHead = User::create([
            'name' => 'Grace Achieng (Campaign Head)',
            'email' => 'campaignhead@church.org',
            'pin' => Hash::make('1234'),
            'role' => 'campaign_head',
            'phone' => '+254 711 700 800',
            'pin_reset_required' => false,
        ]);

        $campaignStaff = User::create([
            'name' => 'Caleb Kiprop (Campaign Team)',
            'email' => 'campaign@church.org',
            'pin' => Hash::make('1234'),
            'role' => 'campaign',
            'phone' => '+254 720 900 100',
            'pin_reset_required' => false,
        ]);

        // Parents
        $parent1 = User::create([
            'name' => 'Sarah Muthoni (Parent)',
            'email' => 'parent@church.org',
            'pin' => Hash::make('1234'),
            'role' => 'parent',
            'phone' => '+254 712 345 678',
            'pin_reset_required' => false,
        ]);

        $parent2 = User::create([
            'name' => 'Robert Otieno',
            'email' => 'robert.otieno@example.com',
            'pin' => Hash::make('1234'),
            'role' => 'parent',
            'phone' => '+254 721 888 999',
            'pin_reset_required' => false,
        ]);

        // Teens
        $teen1 = User::create([
            'name' => 'Ethan Kamau',
            'email' => 'teen@church.org',
            'pin' => Hash::make('1234'),
            'role' => 'teen',
            'phone' => '+254 712 000 111',
            'gender' => 'male',
            'date_of_birth' => '2010-04-15',
            'pin_reset_required' => false,
        ]);

        $teen2 = User::create([
            'name' => 'Chloe Wambui',
            'email' => 'chloe.wambui@example.com',
            'pin' => Hash::make('1234'),
            'role' => 'teen',
            'phone' => '+254 712 000 222',
            'gender' => 'female',
            'date_of_birth' => '2012-08-20',
            'pin_reset_required' => false,
        ]);

        $newTeen = User::create([
            'name' => 'Lucas Baraka (First-Login Demo)',
            'email' => 'newteen@church.org',
            'pin' => Hash::make('0000'),
            'role' => 'teen',
            'phone' => '+254 712 000 333',
            'gender' => 'male',
            'date_of_birth' => '2011-02-10',
            'pin_reset_required' => true,
        ]);

        // Link Parent-Teens
        $parent1->teens()->attach([
            $teen1->id => ['relationship' => 'Mother'],
            $teen2->id => ['relationship' => 'Mother'],
        ]);

        $parent2->teens()->attach([
            $newTeen->id => ['relationship' => 'Father'],
        ]);

        // 2. Create Two Camp Seasons in Kenya
        // Season 2025: Archived Past Season
        $season2025 = CampSeason::create([
            'name' => 'Teen Camp 2025',
            'year' => '2025',
            'theme' => 'Rooted & Grounded in Christ',
            'start_date' => '2025-08-10 09:00:00',
            'end_date' => '2025-08-15 17:00:00',
            'venue' => 'Subukia Shrine & Campsite, Nakuru, Kenya',
            'price' => 12000.00,
            'capacity' => 100,
            'status' => 'archived',
            'description' => 'Historic season 2025 in Nakuru. Data fully archived and preserved.',
            'landing_subtitle' => 'Unshakable in truth, united in fellowship.',
        ]);

        // Season 2026: Active Current Season
        $season2026 = CampSeason::create([
            'name' => 'Teen Camp 2026',
            'year' => '2026',
            'theme' => 'Unstoppable: Faith in Motion',
            'start_date' => now()->addDays(24)->setTime(8, 30),
            'end_date' => now()->addDays(29)->setTime(16, 0),
            'venue' => 'Brackenhurst Conference & Adventure Center, Limuru, Kenya',
            'price' => 15000.00,
            'capacity' => 120,
            'status' => 'active',
            'description' => 'Join us for a transformative 5-day mountain retreat in Limuru featuring worship, obstacle courses, team challenges, and deep fellowship!',
            'landing_subtitle' => 'Live with bold conviction and discover your God-given purpose in Kenya.',
            'announcement_banner' => 'M-Pesa payment Till 5412345 & Paybill 880100 are active. Please clear balances early.',
        ]);

        // 3. Populate 2025 Archived Season Data (Isolated from 2026)
        $reg2025 = Registration::create([
            'camp_season_id' => $season2025->id,
            'teen_id' => $teen1->id,
            'registered_by' => $admin->id,
            'status' => 'signed_in',
            'phone_carried' => false,
            'medication_notes' => 'None',
            'medical_conditions' => 'None',
            'emergency_contact_name' => $parent1->name,
            'emergency_contact_phone' => $parent1->phone,
            'signed_in_at' => '2025-08-10 10:15:00',
            'signed_in_by' => $registrationStaff->id,
        ]);

        $receipt2025 = Receipt::create([
            'camp_season_id' => $season2025->id,
            'receipt_number' => 'REC-20250801-10021',
            'type' => 'direct_payment',
            'user_id' => $parent1->id,
            'amount' => 12000.00,
            'description' => '2025 Camp fee for Ethan Kamau via M-Pesa',
            'meta_data' => [
                'payment_method' => 'M-Pesa',
                'mpesa_code' => 'QHK78X9921',
            ],
        ]);

        Payment::create([
            'camp_season_id' => $season2025->id,
            'registration_id' => $reg2025->id,
            'parent_id' => $parent1->id,
            'amount' => 12000.00,
            'source' => 'direct_payment',
            'reference' => 'QHK78X9921',
            'receipt_number' => $receipt2025->receipt_number,
            'payment_method' => 'M-Pesa Paybill',
            'status' => 'completed',
        ]);

        AdoptATeenKitty::create([
            'camp_season_id' => $season2025->id,
            'type' => 'donation_in',
            'amount' => 30000.00,
            'balance_after' => 30000.00,
            'description' => '2025 Memorial M-Pesa Gift',
            'reference' => 'MPESA-DON-2025',
        ]);

        // 4. Populate 2026 Active Season Data
        $reg1 = Registration::create([
            'camp_season_id' => $season2026->id,
            'teen_id' => $teen1->id,
            'registered_by' => $registrationStaff->id,
            'status' => 'registered',
            'phone_carried' => false,
            'medication_notes' => 'Inhaler for mild sports asthma (carried in backpack)',
            'medical_conditions' => 'Mild seasonal asthma, Penicillin allergy',
            'emergency_contact_name' => $parent1->name,
            'emergency_contact_phone' => $parent1->phone,
        ]);

        $reg2 = Registration::create([
            'camp_season_id' => $season2026->id,
            'teen_id' => $teen2->id,
            'registered_by' => $registrationStaff->id,
            'status' => 'registered',
            'phone_carried' => true,
            'medication_notes' => 'None',
            'medical_conditions' => 'None',
            'emergency_contact_name' => $parent1->name,
            'emergency_contact_phone' => $parent1->phone,
        ]);

        $reg3 = Registration::create([
            'camp_season_id' => $season2026->id,
            'teen_id' => $newTeen->id,
            'registered_by' => $registrationStaff->id,
            'status' => 'registered',
            'phone_carried' => false,
            'medication_notes' => 'None',
            'medical_conditions' => 'None',
            'emergency_contact_name' => $parent2->name,
            'emergency_contact_phone' => $parent2->phone,
        ]);

        // 2026 Payments: Ethan paid KES 10,000 via M-Pesa (needs KES 5,000), Chloe applies for adopt-a-teen
        $rec1 = Receipt::create([
            'camp_season_id' => $season2026->id,
            'receipt_number' => 'DIR-20260901-44012',
            'type' => 'direct_payment',
            'user_id' => $parent1->id,
            'amount' => 10000.00,
            'description' => 'M-Pesa Camp deposit for Ethan Kamau',
            'meta_data' => [
                'payment_method' => 'M-Pesa Till 5412345',
                'mpesa_code' => 'RJG82K9102',
            ],
        ]);

        Payment::create([
            'camp_season_id' => $season2026->id,
            'registration_id' => $reg1->id,
            'parent_id' => $parent1->id,
            'amount' => 10000.00,
            'source' => 'direct_payment',
            'reference' => 'RJG82K9102',
            'receipt_number' => $rec1->receipt_number,
            'payment_method' => 'M-Pesa',
            'status' => 'completed',
            'created_by' => $parent1->id,
        ]);

        // 2026 Adopt-a-Teen Kitty Initial Donations via M-Pesa
        $kittyDonationReceipt = Receipt::create([
            'camp_season_id' => $season2026->id,
            'receipt_number' => 'KIT-20260905-88123',
            'type' => 'kitty_donation_in',
            'amount' => 50000.00,
            'description' => 'Adopt-a-Teen Seed Grant via M-Pesa Paybill from Church Missions Board',
            'meta_data' => [
                'payment_method' => 'M-Pesa Paybill',
                'mpesa_code' => 'SHK99L2034',
            ],
        ]);

        AdoptATeenKitty::create([
            'camp_season_id' => $season2026->id,
            'type' => 'donation_in',
            'amount' => 50000.00,
            'balance_after' => 50000.00,
            'description' => 'Missions Board Camp Sponsorship Grant (M-Pesa SHK99L2034)',
            'reference' => 'SHK99L2034',
            'receipt_id' => $kittyDonationReceipt->id,
            'created_by' => $admin->id,
        ]);

        // Adopt-a-Teen Request for Chloe Wambui
        AdoptATeenRequest::create([
            'camp_season_id' => $season2026->id,
            'parent_id' => $parent1->id,
            'teen_id' => $teen2->id,
            'amount_requested' => 15000.00,
            'reason' => 'Single parent covering 2 high-school campers this season. Requesting full church assistance for Chloe.',
            'status' => 'pending',
        ]);

        // 5. Campaign Products in Kenya (KES)
        $p1 = CampaignProduct::create([
            'camp_season_id' => $season2026->id,
            'name' => 'Camp 2026 Stainless Water Bottle',
            'unit_cost' => 600.00,
            'unit_price' => 1500.00,
            'profit_per_unit' => 900.00,
            'is_active' => true,
        ]);

        $p2 = CampaignProduct::create([
            'camp_season_id' => $season2026->id,
            'name' => 'Unstoppable Faith Heavy Hoodie',
            'unit_cost' => 1800.00,
            'unit_price' => 3500.00,
            'profit_per_unit' => 1700.00,
            'is_active' => true,
        ]);

        $p3 = CampaignProduct::create([
            'camp_season_id' => $season2026->id,
            'name' => 'Gospel Wristbands (Pack of 3)',
            'unit_cost' => 150.00,
            'unit_price' => 500.00,
            'profit_per_unit' => 350.00,
            'is_active' => true,
        ]);

        // Campaign Weekly Batch (Holding Record)
        $batch = CampaignWeeklyBatch::create([
            'camp_season_id' => $season2026->id,
            'week_label' => '2026 - Week ' . now()->weekOfYear,
            'week_start' => now()->startOfWeek(),
            'week_end' => now()->endOfWeek(),
            'total_sales_amount' => 15000.00,
            'total_profit_amount' => 8800.00,
            'status' => 'pending_review',
        ]);

        CampaignSale::create([
            'camp_season_id' => $season2026->id,
            'batch_id' => $batch->id,
            'seller_id' => $campaignStaff->id,
            'product_id' => $p1->id,
            'quantity' => 4,
            'unit_price' => 1500.00,
            'total_amount' => 6000.00,
            'unit_profit' => 900.00,
            'total_profit' => 3600.00,
            'sold_at' => now()->subHours(5),
        ]);

        CampaignSale::create([
            'camp_season_id' => $season2026->id,
            'batch_id' => $batch->id,
            'seller_id' => $campaignStaff->id,
            'product_id' => $p2->id,
            'quantity' => 2,
            'unit_price' => 3500.00,
            'total_amount' => 7000.00,
            'unit_profit' => 1700.00,
            'total_profit' => 3400.00,
            'sold_at' => now()->subHours(2),
        ]);

        // 6. Dynamic Forms for 2026
        $form1 = Form::create([
            'camp_season_id' => $season2026->id,
            'title' => 'Camp Medical & Dietary Profile',
            'description' => 'Mandatory Kenyan camp medical declaration. Requires parental sign-off before submission.',
            'target_role' => 'both',
            'requires_parent_approval' => true,
            'is_published' => true,
            'created_by' => $registrationStaff->id,
        ]);

        FormField::create([
            'form_id' => $form1->id,
            'label' => 'Dietary Preference / Food Restrictions',
            'field_type' => 'dropdown',
            'options' => ['No Dietary Restrictions', 'Vegetarian', 'Nut Allergy Safe', 'Gluten-Free', 'Halal Diet'],
            'is_required' => true,
            'order_index' => 0,
        ]);

        FormField::create([
            'form_id' => $form1->id,
            'label' => 'Can you swim in deep swimming pool / lake water?',
            'field_type' => 'multiple_choice',
            'options' => ['Strong Swimmer', 'Intermediate', 'Non-Swimmer (Lifejacket Required)'],
            'is_required' => true,
            'order_index' => 1,
        ]);

        FormField::create([
            'form_id' => $form1->id,
            'label' => 'Emergency Hospital Preference in Nairobi / Kiambu / Limuru',
            'field_type' => 'text',
            'is_required' => false,
            'order_index' => 2,
        ]);

        // Seed a sample submission for Ethan requiring parent sign-off
        $submission = FormSubmission::create([
            'form_id' => $form1->id,
            'camp_season_id' => $season2026->id,
            'user_id' => $teen1->id,
            'teen_id' => $teen1->id,
            'status' => 'pending_parent_review',
        ]);

        FormSubmissionValue::create([
            'form_submission_id' => $submission->id,
            'form_field_id' => $form1->fields[0]->id,
            'value' => 'No Dietary Restrictions',
        ]);

        FormSubmissionValue::create([
            'form_submission_id' => $submission->id,
            'form_field_id' => $form1->fields[1]->id,
            'value' => 'Intermediate',
        ]);

        // 7. Packing List Items for 2026 (Limuru mountain weather in Kenya)
        $packingItems = [
            ['category' => 'Clothing & Footwear', 'item' => '5x Daily T-Shirts & Trackpants/Jeans', 'essential' => true, 'notes' => 'Modest camp athletic attire'],
            ['category' => 'Clothing & Footwear', 'item' => '2x Warm Sweaters / Hoodies / Jackets', 'essential' => true, 'notes' => 'Limuru gets very cold at night (12-14°C)'],
            ['category' => 'Clothing & Footwear', 'item' => '2x Pairs of Sneakers / Hiking Shoes', 'essential' => true, 'notes' => 'For muddy tea plantation hikes'],
            ['category' => 'Bedding & Sleep', 'item' => 'Warm Sleeping Bag or Heavy Maasai Shuka + Duvet', 'essential' => true, 'notes' => 'Bunks provided with mattresses'],
            ['category' => 'Bedding & Sleep', 'item' => 'Pillow with Pillowcase', 'essential' => true, 'notes' => ''],
            ['category' => 'Toiletries & Hygiene', 'item' => '2x Quick-Dry Towels', 'essential' => true, 'notes' => ''],
            ['category' => 'Toiletries & Hygiene', 'item' => 'Toothbrush, Toothpaste, Bath Soap, Vaseline', 'essential' => true, 'notes' => ''],
            ['category' => 'Bible & Stationery', 'item' => 'Physical Swahili/English Bible & Notebook', 'essential' => true, 'notes' => 'No phone Bibles during services'],
            ['category' => 'Bible & Stationery', 'item' => 'Pens & Highlighters', 'essential' => false, 'notes' => ''],
            ['category' => 'Special Items', 'item' => 'Refillable Water Bottle', 'essential' => true, 'notes' => 'Hydration during afternoon sports'],
            ['category' => 'Special Items', 'item' => 'Torch / Flashlight + Extra Batteries', 'essential' => true, 'notes' => 'For evening outdoor bonfires'],
        ];

        foreach ($packingItems as $pi) {
            PackingList::create([
                'camp_season_id' => $season2026->id,
                'category' => $pi['category'],
                'item_name' => $pi['item'],
                'notes' => $pi['notes'],
                'is_essential' => $pi['essential'],
                'is_released' => true,
            ]);
        }

        // 8. Notifications (with M-Pesa details & rich event trail)
        Notification::create([
            'camp_season_id' => $season2026->id,
            'user_id' => null,
            'target_role' => 'all',
            'title' => 'Karibu Teen Camp 2026!',
            'message' => 'Registration is officially open in Kenya! M-Pesa Till 5412345 & Paybill 880100 are active.',
            'type' => 'system',
            'icon' => 'bi-megaphone-fill text-danger',
            'created_by' => $admin->id,
        ]);

        Notification::create([
            'camp_season_id' => $season2026->id,
            'user_id' => $parent1->id,
            'target_role' => 'parent',
            'title' => 'Form Sign-Off Requested for Ethan Kamau',
            'message' => 'Ethan has filled out the Camp Medical Profile form. Please review and sign off from your parent dashboard.',
            'type' => 'form',
            'icon' => 'bi-file-earmark-arrow-up-fill text-warning',
            'created_by' => $teen1->id,
        ]);

        Notification::create([
            'camp_season_id' => $season2026->id,
            'user_id' => $parent1->id,
            'target_role' => 'parent',
            'title' => 'M-Pesa Payment Received',
            'message' => 'Tuition payment of KES 12,000.00 confirmed via Till 5412345 for Ethan Kamau. Receipt #TC26-MPESA-1001.',
            'type' => 'payment',
            'icon' => 'bi-cash-stack text-success',
            'created_by' => $parent1->id,
        ]);

        Notification::create([
            'camp_season_id' => $season2026->id,
            'user_id' => $campaignStaff->id,
            'target_role' => 'staff',
            'title' => 'Merchandise Sale Logged',
            'message' => 'Caleb Kiprop logged sale of 3x Camp T-Shirt 2026 (KES 3,600.00) in Batch #1.',
            'type' => 'campaign_sale',
            'icon' => 'bi-bag-check-fill text-warning',
            'created_by' => $campaignStaff->id,
        ]);

        Notification::create([
            'camp_season_id' => $season2026->id,
            'user_id' => $admin->id,
            'target_role' => 'staff',
            'title' => 'Camper Check-in Recorded',
            'message' => 'Ethan Kamau checked in at Brackenhurst Conference & Adventure Center at 08:30 AM.',
            'type' => 'checkin',
            'icon' => 'bi-geo-alt-fill text-success',
            'created_by' => $registrationStaff->id,
        ]);
    }
}
