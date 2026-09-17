<?php

namespace Tests\Feature;

use App\Models\AdoptATeenKitty;
use App\Models\AdoptATeenRequest;
use App\Models\CampaignProduct;
use App\Models\CampaignWeeklyBatch;
use App\Models\CampSeason;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Notification;
use App\Models\PackingList;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CampSystemTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_public_landing_page_displays_active_season(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Teen Camp 2026');
        $response->assertSee('Unstoppable: Faith in Motion');
        $response->assertSee('Brackenhurst Conference & Adventure Center, Limuru, Kenya');
        $response->assertSee('Starts in:');
        $response->assertSee('KES 15,000.00');
        $response->assertSee('Admin Log In');
        $response->assertSee('Register');
        $response->assertSee('Sign In');
    }

    public function test_public_participant_login_with_pin(): void
    {
        $response = $this->post('/login', [
            'email' => 'parent@church.org',
            'pin' => '1234',
        ]);

        $response->assertRedirect('/parent/dashboard');
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertEquals('parent', Auth::guard('web')->user()->role);
    }

    public function test_first_login_pin_setup_flow(): void
    {
        // Login with newteen (PIN 0000, pin_reset_required = true)
        $response = $this->post('/login', [
            'email' => 'newteen@church.org',
            'pin' => '0000',
        ]);

        $response->assertRedirect(route('public.pin.setup'));

        // Set new 4-digit PIN
        $setupResponse = $this->actingAs(User::where('email', 'newteen@church.org')->first(), 'web')
            ->post('/set-pin', [
                'pin' => '4321',
                'pin_confirmation' => '4321',
            ]);

        $setupResponse->assertRedirect('/teen/dashboard');

        $updatedTeen = User::where('email', 'newteen@church.org')->first();
        $this->assertFalse($updatedTeen->pin_reset_required);
        $this->assertTrue(Hash::check('4321', $updatedTeen->pin));
    }

    public function test_teen_dashboard_displays_view_only_payment_and_released_packing_list(): void
    {
        $teen = User::where('email', 'teen@church.org')->first();

        $response = $this->actingAs($teen, 'web')->get('/teen/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Ethan Kamau');
        $response->assertSee('Camp Fee Status');
        $response->assertSee('View Only');
        $response->assertSee('Camp Packing Checklist');
        $response->assertSee('Refillable Water Bottle');
        $response->assertSee('KES');
    }

    public function test_parent_payment_and_receipt_generation(): void
    {
        $parent = User::where('email', 'parent@church.org')->first();
        $season = CampSeason::getActive();
        $registration = Registration::where('camp_season_id', $season->id)
            ->whereHas('teen', fn($q) => $q->where('email', 'teen@church.org'))
            ->first();

        $initialBalance = $registration->balance_remaining;

        $response = $this->actingAs($parent, 'web')->post('/parent/pay', [
            'registration_id' => $registration->id,
            'amount' => 5000.00,
            'payment_method' => 'M-Pesa (Till 5412345)',
            'mpesa_code' => 'QA94XD8712',
            'mpesa_phone' => '+254 712 345 678',
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('confirmed_receipt_number');
        $receiptNumber = session('confirmed_receipt_number');

        $registration->refresh();
        $this->assertEquals($initialBalance - 5000.00, $registration->balance_remaining);
        $this->assertDatabaseHas('payments', [
            'registration_id' => $registration->id,
            'amount' => 5000.00,
            'reference' => 'QA94XD8712',
            'payment_method' => 'M-Pesa (Till 5412345)',
        ]);

        // Assert receipt created
        $this->assertDatabaseHas('receipts', [
            'receipt_number' => $receiptNumber,
            'amount' => 5000.00,
            'user_id' => $parent->id,
        ]);

        // Assert parent notification created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $parent->id,
            'type' => 'payment',
        ]);

        // Assert staff notification created for backend team
        $this->assertDatabaseHas('notifications', [
            'target_role' => 'staff',
            'type' => 'payment',
        ]);

        // Assert parent can view official receipt
        $receiptResp = $this->actingAs($parent, 'web')->get("/receipts/{$receiptNumber}");
        $receiptResp->assertStatus(200);
        $receiptResp->assertSee($receiptNumber);
        $receiptResp->assertSee('KES 5,000.00', false);
        $receiptResp->assertSee('QA94XD8712');

        // Assert backend staff can view official receipt
        $admin = User::where('role', 'admin')->first();
        $staffReceiptResp = $this->actingAs($admin, 'staff')->get("/receipts/{$receiptNumber}");
        $staffReceiptResp->assertStatus(200);
        $staffReceiptResp->assertSee($receiptNumber);
    }

    public function test_parent_form_approval_flow(): void
    {
        $parent = User::where('email', 'parent@church.org')->first();
        $pendingSubmission = FormSubmission::where('status', 'pending_parent_review')->first();

        $this->assertNotNull($pendingSubmission);

        $response = $this->actingAs($parent, 'web')->post("/parent/forms/approve/{$pendingSubmission->id}", [
            'notes' => 'Medical details verified by parent',
        ]);

        $response->assertSessionHas('success');
        $pendingSubmission->refresh();
        $this->assertEquals('submitted', $pendingSubmission->status);
        $this->assertNotNull($pendingSubmission->reviewed_at);
    }

    public function test_staff_backoffice_login_and_role_protection(): void
    {
        // Public participant cannot access backoffice
        $parent = User::where('email', 'parent@church.org')->first();
        $response = $this->post('/backoffice/login', [
            'email' => $parent->email,
            'pin' => '1234',
        ]);
        $response->assertSessionHasErrors('email');

        // Admin can login
        $admin = User::where('email', 'admin@church.org')->first();
        $adminResponse = $this->post('/backoffice/login', [
            'email' => $admin->email,
            'pin' => '1234',
        ]);
        $adminResponse->assertRedirect(route('backoffice.admin.dashboard'));
    }

    public function test_admin_dashboard_interactive_deck_renders(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        $response = $this->actingAs($admin, 'staff')->get('/backoffice/admin');
        $response->assertStatus(200);
        $response->assertSee('System Admin Command Deck');
        $response->assertSee('Paybill');
        $response->assertSee('Camp Attendance');
        $response->assertSee('Tuition Collected');
        $response->assertSee('Adopt-a-Teen Kitty');
        $response->assertSee('Live Camper Roster');
        $response->assertSee('Logistics Watch');
    }

    public function test_admin_database_search_and_filter_renders(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        $response = $this->actingAs($admin, 'staff')->get('/backoffice/admin/database');
        $response->assertStatus(200);
        $response->assertSee('Camper Database');
        $response->assertSee('Keyword Search');
        $response->assertSee('Camp Season');
        $response->assertSee('Medical / Allergy');
    }

    public function test_admin_adopt_a_teen_approval_deducts_kitty_and_records_payment(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $season = CampSeason::getActive();

        $initialKitty = AdoptATeenKitty::getCurrentBalance($season->id);
        $this->assertGreaterThanOrEqual(500.00, $initialKitty);

        $adoptReq = AdoptATeenRequest::where('camp_season_id', $season->id)
            ->where('status', 'pending')
            ->first();

        $this->assertNotNull($adoptReq);

        $response = $this->actingAs($admin, 'staff')->post("/backoffice/admin/adopt-requests/{$adoptReq->id}/review", [
            'action' => 'approve',
            'decision_notes' => 'Approved full scholarship from mission kitty fund',
        ]);

        $response->assertSessionHas('success');

        $adoptReq->refresh();
        $this->assertEquals('approved', $adoptReq->status);

        $newKitty = AdoptATeenKitty::getCurrentBalance($season->id);
        $this->assertEquals($initialKitty - $adoptReq->amount_requested, $newKitty);

        // Verify payment recorded against teen registration
        $this->assertDatabaseHas('payments', [
            'camp_season_id' => $season->id,
            'amount' => $adoptReq->amount_requested,
            'source' => 'adopt_a_teen_transfer',
        ]);
    }

    public function test_adopt_a_teen_portal_renders_for_backend_staff(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        $response = $this->actingAs($admin, 'staff')->get('/backoffice/adopt-a-teen');
        $response->assertStatus(200);
        $response->assertSee('Adopt-a-Teen Portal');
        $response->assertSee('Active Kitty Pool');
        $response->assertSee('Aid Applications Queue');
        $response->assertSee('Sponsor a Camper (Matchmaker)');
        $response->assertSee('Kitty Financial Audit Ledger');
    }

    public function test_adopt_a_teen_portal_donation_and_direct_sponsor(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $season = CampSeason::getActive();

        // 1. Record direct donation in portal
        $initialKitty = AdoptATeenKitty::getCurrentBalance($season->id);
        $donationResponse = $this->actingAs($admin, 'staff')->post('/backoffice/adopt-a-teen/donation', [
            'amount' => 7000.00,
            'donor_name' => 'Grace Ministry Fellowship',
            'reference' => 'QA94DONATE77',
            'notes' => 'Camp sponsorship pool tithe',
        ]);
        $donationResponse->assertSessionHas('success');
        $this->assertEquals($initialKitty + 7000.00, AdoptATeenKitty::getCurrentBalance($season->id));

        // 2. Direct sponsor a camper
        $unsponsoredReg = Registration::where('camp_season_id', $season->id)
            ->where('balance_remaining', '>', 0)
            ->first();

        if ($unsponsoredReg) {
            $sponsorAmount = min(5000.00, $unsponsoredReg->balance_remaining);
            $sponsorResponse = $this->actingAs($admin, 'staff')->post('/backoffice/adopt-a-teen/direct-sponsor', [
                'registration_id' => $unsponsoredReg->id,
                'amount' => $sponsorAmount,
                'sponsor_type' => 'kitty_pool',
                'sponsor_name' => 'Church Youth Mission Fund',
            ]);
            $sponsorResponse->assertSessionHas('success');
            $this->assertDatabaseHas('payments', [
                'registration_id' => $unsponsoredReg->id,
                'amount' => $sponsorAmount,
                'source' => 'adopt_a_teen_transfer',
            ]);
        }
    }

    public function test_campaign_batch_approval_transfers_profit_to_kitty(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $season = CampSeason::getActive();

        $batch = CampaignWeeklyBatch::where('camp_season_id', $season->id)
            ->where('status', 'pending_review')
            ->first();

        $this->assertNotNull($batch);
        $profit = $batch->total_profit_amount;

        $kittyBefore = AdoptATeenKitty::getCurrentBalance($season->id);

        $response = $this->actingAs($admin, 'staff')->post("/backoffice/campaign/batches/{$batch->id}/approve", [
            'notes' => 'Weekly merchandise sales verified',
        ]);

        $response->assertSessionHas('success');

        $batch->refresh();
        $this->assertEquals('approved', $batch->status);

        $kittyAfter = AdoptATeenKitty::getCurrentBalance($season->id);
        $this->assertEquals($kittyBefore + $profit, $kittyAfter);
    }

    public function test_desk_registration_2_minute_intake_creates_accounts(): void
    {
        $staff = User::where('email', 'registration@church.org')->first();

        $response = $this->actingAs($staff, 'staff')->post('/backoffice/registration/desk', [
            'parent_name' => 'Samuel Adams Kiprono',
            'parent_email' => 'samuel.adams@test.org',
            'parent_phone' => '+254 722 987 654',
            'relationship' => 'Father',
            'teen_name' => 'Benjamin Adams Kiprono',
            'teen_email' => 'benjamin.adams@test.org',
            'teen_gender' => 'male',
            'teen_dob' => '2011-06-18',
            'phone_carried' => '1',
            'medical_conditions' => 'Bee sting allergy (EpiPen in pouch)',
            'initial_payment' => 5000.00,
            'payment_method' => 'M-Pesa (Till 5412345)',
            'payment_reference' => 'SK88L29103',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Registration Successful!');
        $response->assertSee('Benjamin Adams');
        $response->assertSee('Samuel Adams');
        $response->assertSee('Default PIN:');
        $response->assertSee('0000');

        // Verify accounts created with forced PIN reset
        $parent = User::where('email', 'samuel.adams@test.org')->first();
        $teen = User::where('email', 'benjamin.adams@test.org')->first();

        $this->assertNotNull($parent);
        $this->assertNotNull($teen);
        $this->assertTrue($parent->pin_reset_required);
        $this->assertTrue($teen->pin_reset_required);
        $this->assertTrue($parent->teens->contains($teen));
    }

    public function test_staff_can_edit_2_minute_desk_registration_contents(): void
    {
        $staff = User::where('email', 'registration@church.org')->first();
        $season = CampSeason::getActive();
        $registration = Registration::where('camp_season_id', $season->id)
            ->with(['teen.parents'])
            ->first();

        $this->assertNotNull($registration);
        $teen = $registration->teen;
        $parent = $teen->parents->first();

        // 1. Staff visits edit view
        $editResponse = $this->actingAs($staff, 'staff')->get("/backoffice/registration/{$registration->id}/edit");
        $editResponse->assertStatus(200);
        $editResponse->assertSee("Edit Desk Registration: {$teen->name}");

        // 2. Staff updates camper and parent details
        $updateResponse = $this->actingAs($staff, 'staff')->put("/backoffice/registration/{$registration->id}", [
            'parent_name' => 'Updated Parent Name',
            'parent_email' => $parent->email,
            'parent_phone' => '+254 799 111 222',
            'relationship' => 'Mother',
            'teen_name' => 'Updated Teen Name',
            'teen_email' => $teen->email,
            'teen_gender' => 'male',
            'teen_dob' => '2010-05-12',
            'teen_phone' => '+254 788 333 444',
            'phone_carried' => '1',
            'medication_notes' => 'Daily allergy antihistamine at bedtime',
            'medical_conditions' => 'Pollen & dust allergy',
            'emergency_contact_name' => 'Updated Emergency Contact',
            'emergency_contact_phone' => '+254 799 111 222',
            'status' => 'registered',
            'notes' => 'Updated through backoffice 2-minute desk edit.',
        ]);

        $updateResponse->assertRedirect(route('backoffice.registration.desk'));
        $updateResponse->assertSessionHas('success');

        // 3. Verify database updates
        $teen->refresh();
        $parent->refresh();
        $registration->refresh();

        $this->assertEquals('Updated Teen Name', $teen->name);
        $this->assertEquals('Updated Parent Name', $parent->name);
        $this->assertEquals('+254 799 111 222', $parent->phone);
        $this->assertTrue($registration->phone_carried);
        $this->assertEquals('Daily allergy antihistamine at bedtime', $registration->medication_notes);
        $this->assertEquals('Pollen & dust allergy', $registration->medical_conditions);
        $this->assertEquals('Updated Emergency Contact', $registration->emergency_contact_name);

        // Verify parent and staff notifications
        $this->assertDatabaseHas('notifications', [
            'user_id' => $parent->id,
            'type' => 'registration',
        ]);
    }

    public function test_public_user_can_register_from_landing_page_via_2_minute_form(): void
    {
        $season = CampSeason::getActive();

        // 1. Visit public registration page
        $pageResponse = $this->get('/register-camper');
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Express Camper Registration');
        $pageResponse->assertSee($season->name);

        // 2. Submit 2-minute intake with M-Pesa payment
        $response = $this->post('/register-camper', [
            'parent_name' => 'David Ochieng',
            'parent_email' => 'david.ochieng@gmail.com',
            'parent_phone' => '+254 722 998 877',
            'relationship' => 'Father',
            'teen_name' => 'Joy Ochieng',
            'teen_email' => 'joy.ochieng@gmail.com',
            'teen_gender' => 'female',
            'teen_dob' => '2011-09-05',
            'teen_phone' => '+254 733 445 566',
            'phone_carried' => '1',
            'medication_notes' => 'None',
            'medical_conditions' => 'Mild lactose intolerance',
            'initial_payment' => 5000.00,
            'payment_method' => 'M-Pesa (Till 5412345)',
            'payment_reference' => 'MP-ONLINE771',
        ]);

        // Auto-logs in parent and redirects to parent dashboard
        $response->assertRedirect('/parent/dashboard');
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertEquals('david.ochieng@gmail.com', Auth::guard('web')->user()->email);

        // Verify DB records
        $parent = User::where('email', 'david.ochieng@gmail.com')->first();
        $teen = User::where('email', 'joy.ochieng@gmail.com')->first();

        $this->assertNotNull($parent);
        $this->assertNotNull($teen);
        $this->assertTrue($parent->pin_reset_required);
        $this->assertTrue($teen->pin_reset_required);

        $reg = Registration::where('camp_season_id', $season->id)
            ->where('teen_id', $teen->id)
            ->first();

        $this->assertNotNull($reg);
        $this->assertTrue($reg->phone_carried);
        $this->assertEquals('Mild lactose intolerance', $reg->medical_conditions);
        $this->assertEquals(5000.00, $reg->total_paid);

        // Verify payment and receipt
        $payment = Payment::where('registration_id', $reg->id)->first();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->receipt_number);
        $this->assertDatabaseHas('receipts', [
            'receipt_number' => $payment->receipt_number,
            'user_id' => $parent->id,
        ]);
    }

    public function test_camp_day_one_click_sign_in(): void
    {
        $staff = User::where('email', 'registration@church.org')->first();
        $season = CampSeason::getActive();

        $reg = Registration::where('camp_season_id', $season->id)
            ->where('status', 'registered')
            ->first();

        $this->assertNotNull($reg);

        $response = $this->actingAs($staff, 'staff')->post("/backoffice/registration/sign-in/{$reg->id}");
        $response->assertSessionHas('success');

        $reg->refresh();
        $this->assertEquals('signed_in', $reg->status);
        $this->assertNotNull($reg->signed_in_at);
        $this->assertEquals($staff->id, $reg->signed_in_by);
    }

    public function test_multi_season_isolation(): void
    {
        $season2025 = CampSeason::where('year', '2025')->first();
        $season2026 = CampSeason::where('year', '2026')->first();

        $this->assertNotNull($season2025);
        $this->assertNotNull($season2026);
        $this->assertEquals('archived', $season2025->status);
        $this->assertEquals('active', $season2026->status);

        // Verify registrations in 2025 are completely isolated from 2026
        $regs2025 = Registration::where('camp_season_id', $season2025->id)->pluck('id');
        $regs2026 = Registration::where('camp_season_id', $season2026->id)->pluck('id');

        $intersection = $regs2025->intersect($regs2026);
        $this->assertEmpty($intersection);

        // Verify Ethan Jenkins has 2 independent registration records across both seasons
        $ethan = User::where('email', 'teen@church.org')->first();
        $this->assertCount(2, $ethan->registrations);

        // Verify kitty records in 2025 are separate from 2026
        $kitty2025 = AdoptATeenKitty::getCurrentBalance($season2025->id);
        $kitty2026 = AdoptATeenKitty::getCurrentBalance($season2026->id);

        $this->assertEquals(30000.00, $kitty2025);
        $this->assertNotEquals($kitty2025, $kitty2026);
    }

    public function test_user_actions_create_database_notifications_and_audit_stream(): void
    {
        $parent = User::where('email', 'parent@church.org')->first();
        $season = CampSeason::getActive();
        $registration = Registration::where('camp_season_id', $season->id)->first();

        // 1. Parent makes a payment
        $this->actingAs($parent, 'web')->post('/parent/pay', [
            'registration_id' => $registration->id,
            'amount' => 3500.00,
            'payment_method' => 'M-Pesa (Till 5412345)',
            'mpesa_code' => 'QK77TEST01',
        ]);

        // Verify notification appended to DB for parent and staff
        $this->assertDatabaseHas('notifications', [
            'type' => 'payment',
            'user_id' => $parent->id,
        ]);

        // 2. Campaign staff records sale
        $campaignStaff = User::where('email', 'campaign@church.org')->first();
        $product = CampaignProduct::where('camp_season_id', $season->id)->first();
        $batch = CampaignWeeklyBatch::where('camp_season_id', $season->id)->first();

        $this->actingAs($campaignStaff, 'staff')->post('/backoffice/campaign/sale', [
            'product_id' => $product->id,
            'quantity' => 2,
            'batch_id' => $batch->id,
        ]);

        // Verify notification appended to DB for campaign sale
        $this->assertDatabaseHas('notifications', [
            'type' => 'campaign_sale',
            'user_id' => $campaignStaff->id,
        ]);

        // 3. Admin dashboard renders activity stream tab and live sync ticker
        $admin = User::where('email', 'admin@church.org')->first();
        $response = $this->actingAs($admin, 'staff')->get('/backoffice/admin');
        $response->assertStatus(200);
        $response->assertSee('Live DB Sync');
        $response->assertSee('Live Activity');
        $response->assertSee('Notification Audit Trail');
    }

    public function test_parent_can_register_new_teen_directly_from_portal(): void
    {
        $parent = User::where('email', 'parent@church.org')->first();
        $season = CampSeason::getActive();

        $response = $this->actingAs($parent, 'web')->post('/parent/register-teen', [
            'teen_name' => 'Grace Wanjiku',
            'teen_gender' => 'female',
            'teen_email' => 'grace.wanjiku@test.local',
            'teen_phone' => '0711999888',
            'emergency_contact_phone' => '0722123456',
            'medical_conditions' => 'Asthma inhaler required',
            'medication_notes' => 'Ventolin twice daily',
            'phone_carried' => '1',
            'initial_payment' => 5000,
            'payment_reference' => 'MPGRACE991',
        ]);

        $response->assertSessionHas('success');

        // Check new teen user exists and is attached to parent
        $teen = User::where('email', 'grace.wanjiku@test.local')->first();
        $this->assertNotNull($teen);
        $this->assertEquals('Grace Wanjiku', $teen->name);
        $this->assertEquals('female', $teen->gender);
        $this->assertTrue($parent->teens->contains($teen->id));
        $response->assertRedirect('/parent/dashboard?child=' . $teen->id);

        // Check registration created for active season
        $reg = Registration::where('teen_id', $teen->id)->where('camp_season_id', $season->id)->first();
        $this->assertNotNull($reg);
        $this->assertEquals('registered', $reg->status);
        $this->assertEquals('Asthma inhaler required', $reg->medical_conditions);
        $this->assertEquals(5000.0, (float)$reg->total_paid);

        // Check initial payment recorded
        $payment = Payment::where('registration_id', $reg->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('MPGRACE991', $payment->reference);
    }

    public function test_admin_can_close_and_reopen_camp_registration(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $season = CampSeason::getActive();

        // 1. Initial state: open
        $this->assertTrue($season->isRegistrationOpen());

        // 2. Admin closes registration
        $response = $this->actingAs($admin, 'staff')->post("/backoffice/admin/seasons/{$season->id}/toggle-registration");
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse($season->fresh()->isRegistrationOpen());

        // 3. Public intake form blocks new submissions when closed
        $postResponse = $this->from('/register-camper')->post('/register-camper', [
            'teen_name' => 'Barred Camper',
            'teen_email' => 'barred@test.local',
            'gender' => 'male',
            'emergency_contact_phone' => '0712345678',
        ]);
        $postResponse->assertRedirect('/register-camper');
        $postResponse->assertSessionHasErrors('error');
        $this->assertNull(User::where('email', 'barred@test.local')->first());

        // 4. Admin re-opens registration
        $this->actingAs($admin, 'staff')->post("/backoffice/admin/seasons/{$season->id}/toggle-registration");
        $this->assertTrue($season->fresh()->isRegistrationOpen());
    }

    public function test_downloadable_pdf_report_endpoints_render_cleanly(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        // 1. Registrations PDF
        $res1 = $this->actingAs($admin, 'staff')->get('/backoffice/reports/registrations/pdf');
        $res1->assertStatus(200);
        $res1->assertSee('Official Campers Registration Roster', false);

        // 2. Sales PDF
        $res2 = $this->actingAs($admin, 'staff')->get('/backoffice/reports/sales/pdf');
        $res2->assertStatus(200);
        $res2->assertSee('Campaign Merchandise Sales', false);

        // 3. Adopt-a-Teen PDF
        $res3 = $this->actingAs($admin, 'staff')->get('/backoffice/reports/adopt/pdf');
        $res3->assertStatus(200);
        $res3->assertSee('Adopt-a-Teen Sponsorship', false);

        // 4. Payments PDF
        $res4 = $this->actingAs($admin, 'staff')->get('/backoffice/reports/payments/pdf');
        $res4->assertStatus(200);
        $res4->assertSee('Camp Fee Payments', false);

        // 5. Database Search PDF
        $res5 = $this->actingAs($admin, 'staff')->get('/backoffice/reports/database/pdf');
        $res5->assertStatus(200);
        $res5->assertSee('Camper Database Query', false);
    }

    public function test_admin_can_suspend_and_reactivate_user_blocking_login(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $parent = User::where('email', 'parent@church.org')->first();

        // 1. Initially active
        $this->assertFalse($parent->isSuspended());

        // 2. Admin suspends parent
        $response = $this->actingAs($admin, 'staff')->post("/backoffice/admin/users/{$parent->id}/toggle-suspension");
        $response->assertRedirect();
        $this->assertTrue($parent->fresh()->isSuspended());

        // 3. Suspended parent cannot log in
        Auth::logout();
        $loginRes = $this->post('/login', [
            'email' => 'parent@church.org',
            'pin' => '1234',
        ]);
        $loginRes->assertRedirect();
        $loginRes->assertSessionHasErrors('email');
        $this->assertFalse(Auth::guard('web')->check());

        // 4. Reactivate parent
        $this->actingAs($admin, 'staff')->post("/backoffice/admin/users/{$parent->id}/toggle-suspension");
        $this->assertFalse($parent->fresh()->isSuspended());

        // 5. Parent can log in again
        Auth::logout();
        $loginSuccess = $this->post('/login', [
            'email' => 'parent@church.org',
            'pin' => '1234',
        ]);
        $loginSuccess->assertRedirect('/parent/dashboard');
        $this->assertTrue(Auth::guard('web')->check());
    }

    public function test_admin_reset_database_requires_correct_pin(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        // 1. Wrong PIN is rejected
        $wrongRes = $this->actingAs($admin, 'staff')->post('/backoffice/admin/reset-database', [
            'admin_pin' => '9999',
            'confirm_text' => 'RESET',
        ]);
        $wrongRes->assertRedirect();
        $wrongRes->assertSessionHas('error');

        // 2. Non-admin cannot call reset
        $campaignStaff = User::where('email', 'campaign@church.org')->first();
        $unauthRes = $this->actingAs($campaignStaff, 'staff')->post('/backoffice/admin/reset-database', [
            'admin_pin' => '1234',
            'confirm_text' => 'RESET',
        ]);
        $unauthRes->assertStatus(403);
    }

    public function test_daraja_stk_push_for_camp_fees_routes_to_camp_fee_account(): void
    {
        $season = CampSeason::getActive();
        $registration = Registration::where('camp_season_id', $season->id)->first();
        $this->assertNotNull($registration);

        $initialPaid = (float)$registration->total_paid;
        $settings = \App\Models\MpesaSetting::getSettings();

        $response = $this->postJson('/mpesa/stk-push', [
            'phone' => '0712345678',
            'amount' => 2500,
            'account_type' => 'camp_fee',
            'registration_id' => $registration->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'account_type' => 'camp_fee',
            'account_reference' => $settings->camp_fee_account,
            'paybill' => $settings->paybill_number,
        ]);

        $registration->refresh();
        $this->assertEquals($initialPaid + 2500, (float)$registration->total_paid);

        $this->assertDatabaseHas('payments', [
            'registration_id' => $registration->id,
            'amount' => 2500,
            'source' => 'direct_payment',
        ]);
    }

    public function test_daraja_stk_push_for_adopt_a_teen_routes_to_adopt_account(): void
    {
        $season = CampSeason::getActive();
        $initialKitty = \App\Models\AdoptATeenKitty::getCurrentBalance($season->id);
        $settings = \App\Models\MpesaSetting::getSettings();

        $response = $this->postJson('/mpesa/stk-push', [
            'phone' => '0722334455',
            'amount' => 3500,
            'account_type' => 'adopt_a_teen',
            'donor_name' => 'Generous Benefactor',
            'reference' => 'ADOPT-WELLWISHER',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'account_type' => 'adopt_a_teen',
            'account_reference' => $settings->adopt_account,
            'paybill' => $settings->paybill_number,
        ]);

        $newKitty = \App\Models\AdoptATeenKitty::getCurrentBalance($season->id);
        $this->assertEquals($initialKitty + 3500, $newKitty);

        $this->assertDatabaseHas('adopt_a_teen_kitty', [
            'camp_season_id' => $season->id,
            'amount' => 3500,
            'type' => 'donation_in',
        ]);
    }

    public function test_admin_can_update_mpesa_paybill_and_dual_accounts(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        $response = $this->actingAs($admin, 'staff')->post('/backoffice/admin/mpesa-settings', [
            'paybill_number' => '889900',
            'camp_fee_account' => 'CAMP-FEES-PRO',
            'adopt_account' => 'ADOPT-BENEVOLENCE',
            'consumer_key' => 'test_key_123',
            'consumer_secret' => 'test_secret_456',
            'passkey' => 'test_passkey_789',
            'environment' => 'sandbox',
            'is_mock_enabled' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mpesa_settings', [
            'paybill_number' => '889900',
            'camp_fee_account' => 'CAMP-FEES-PRO',
            'adopt_account' => 'ADOPT-BENEVOLENCE',
            'environment' => 'sandbox',
            'is_mock_enabled' => 1,
        ]);
    }

    public function test_notification_mark_as_read_via_ajax(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        $notif = \App\Models\Notification::create([
            'user_id' => $admin->id,
            'type' => 'system',
            'title' => 'Test Notification For Read Decrement',
            'message' => 'Testing instant badge update',
            'is_read' => false,
        ]);

        $this->assertFalse((bool)$notif->fresh()->is_read);

        $response = $this->actingAs($admin, 'staff')->postJson("/backoffice/notifications/{$notif->id}/read");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertTrue((bool)$notif->fresh()->is_read);
    }

    public function test_admin_can_view_teen_parent_info_and_siblings(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $parent = User::where('email', 'parent@church.org')->first();
        $teen = User::where('email', 'teen@church.org')->first();

        // Create a sibling for teen under the same parent
        $sibling = User::create([
            'name' => 'Chloe Jenkins',
            'email' => 'chloe.jenkins@family.local',
            'role' => 'teen',
            'pin' => bcrypt('0000'),
            'gender' => 'female',
        ]);
        $parent->teens()->attach($sibling->id, ['relationship' => 'Mother']);

        // 1. Verify User model helper works
        $siblings = $teen->siblings();
        $this->assertTrue($siblings->contains('id', $sibling->id));

        // 2. View users page as admin: should have account management (suspend & delete), but NOT teenFamilyModal
        $response = $this->actingAs($admin, 'staff')->get('/backoffice/admin/users');
        $response->assertStatus(200);
        $response->assertDontSee('teenFamilyModal' . $teen->id);
        $response->assertSee('Suspend Account');
        $response->assertSee('Delete Account Permanently');

        // 3. View database search page as admin: contains teenFamilyModal with parent & sibling info
        $dbResponse = $this->actingAs($admin, 'staff')->get('/backoffice/admin/database');
        $dbResponse->assertStatus(200);
        $dbResponse->assertSee('teenFamilyModal' . $teen->id);
        $dbResponse->assertSee('Siblings Linked to Same Parent');
        $dbResponse->assertSee('Family &amp; Siblings', false);

        // 4. View registration dashboard: also contains teenFamilyModal with parent & sibling info
        $regUser = User::where('role', 'registration')->first();
        $regResponse = $this->actingAs($regUser, 'staff')->get('/backoffice/registration');
        $regResponse->assertStatus(200);
        $regResponse->assertSee('teenFamilyModal' . $teen->id);
        $regResponse->assertSee('Siblings Linked to Same Parent');
        $regResponse->assertSee('Chloe Jenkins');
        $regResponse->assertSee($parent->name);
    }

    public function test_admin_can_update_teen_family_and_parent_info(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $teen = User::where('email', 'teen@church.org')->first();
        $parent = $teen->parents->first();
        $this->assertNotNull($parent);

        $response = $this->actingAs($admin, 'staff')->post("/backoffice/admin/teens/{$teen->id}/family", [
            'name' => 'Ethan Updated Jenkins',
            'email' => $teen->email,
            'phone' => '+254 711 222 333',
            'gender' => 'male',
            'parent_name' => 'Rachel Updated Jenkins',
            'parent_email' => $parent->email,
            'parent_phone' => '+254 722 555 666',
            'parent_relationship' => 'Mother',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Ethan Updated Jenkins', $teen->fresh()->name);
        $this->assertEquals('+254 711 222 333', $teen->fresh()->phone);
        $this->assertEquals('Rachel Updated Jenkins', $parent->fresh()->name);
        $this->assertEquals('+254 722 555 666', $parent->fresh()->phone);
    }

    public function test_admin_can_delete_user_account(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        // Create temporary user to delete
        $tempUser = User::create([
            'name' => 'Temp Deletable User',
            'email' => 'temp.deletable@test.local',
            'role' => 'teen',
            'pin' => bcrypt('0000'),
        ]);

        // 1. Admin deletes temp user
        $response = $this->actingAs($admin, 'staff')->delete("/backoffice/admin/users/{$tempUser->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $tempUser->id]);

        // 2. Admin cannot delete own account
        $selfDeleteResponse = $this->actingAs($admin, 'staff')->delete("/backoffice/admin/users/{$admin->id}");
        $selfDeleteResponse->assertSessionHas('warning');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_reset_user_pin(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $user = User::where('email', 'teen@church.org')->first();

        $response = $this->actingAs($admin, 'staff')->post("/backoffice/admin/users/{$user->id}/reset-pin");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->pin_reset_required);
        $this->assertTrue(Hash::check('0000', $user->pin));

        // Verify security notification created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'security',
        ]);
    }

    public function test_brevo_email_service_and_test_endpoint(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        $response = $this->actingAs($admin, 'staff')->post('/backoffice/admin/test-brevo-email', [
            'email' => 'admin@church.org',
        ]);

        $response->assertRedirect();
        $this->artisan('camp:test-email admin@church.org')->assertSuccessful();
    }

    public function test_admin_and_campaign_head_can_delete_catalogue_item_with_pin(): void
    {
        $season = CampSeason::getActive();
        $head = User::where('role', 'campaign_head')->first();
        $regularStaff = User::where('role', 'campaign')->first();

        // Create a test product
        $product = CampaignProduct::create([
            'camp_season_id' => $season->id,
            'name' => 'Custom Test Cap',
            'unit_cost' => 300,
            'unit_price' => 600,
            'profit_per_unit' => 300,
            'is_active' => true,
        ]);

        // 1. Regular campaign staff cannot delete
        $unauthRes = $this->actingAs($regularStaff, 'staff')->delete("/backoffice/campaign/products/{$product->id}", [
            'pin' => '1234',
        ]);
        $unauthRes->assertStatus(403);
        $this->assertDatabaseHas('campaign_products', ['id' => $product->id]);

        // 2. Campaign Head with wrong PIN fails
        $wrongPinRes = $this->actingAs($head, 'staff')->delete("/backoffice/campaign/products/{$product->id}", [
            'pin' => '9999',
        ]);
        $wrongPinRes->assertRedirect();
        $wrongPinRes->assertSessionHas('error');
        $this->assertDatabaseHas('campaign_products', ['id' => $product->id]);

        // 3. Campaign Head with correct PIN succeeds
        $headRes = $this->actingAs($head, 'staff')->delete("/backoffice/campaign/products/{$product->id}", [
            'pin' => '1234',
        ]);
        $headRes->assertRedirect();
        $headRes->assertSessionHas('success');
        $this->assertDatabaseMissing('campaign_products', ['id' => $product->id]);
    }

    public function test_admin_reset_database_wipes_all_records_and_leaves_only_admin(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();

        // Verify that data currently exists before reset
        $this->assertGreaterThan(1, User::count());
        $this->assertGreaterThan(0, Registration::count());
        $this->assertGreaterThan(0, CampaignProduct::count());

        // Execute reset with admin PIN
        $response = $this->actingAs($admin, 'staff')->post('/backoffice/admin/reset-database', [
            'admin_pin' => '1234',
        ]);

        $response->assertRedirect('/backoffice/admin');
        $response->assertSessionHas('success');

        // Verify ONLY the admin remains in the system
        $this->assertEquals(1, User::count());
        $remainingUser = User::first();
        $this->assertEquals($admin->id, $remainingUser->id);
        $this->assertEquals('admin@church.org', $remainingUser->email);
        $this->assertEquals('admin', $remainingUser->role);

        // Verify all other records are cleaned
        $this->assertEquals(0, Registration::count());
        $this->assertEquals(0, CampaignProduct::count());
        $this->assertEquals(0, AdoptATeenKitty::count());
        $this->assertEquals(0, \App\Models\Payment::count());
        $this->assertEquals(0, \App\Models\Receipt::count());

        // Clean up remaining user before re-seeding so seeder does not collide on unique email
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\User::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    }

    public function test_staff_can_add_edit_and_remove_packing_checklist_items(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $season = CampSeason::getActive();

        // 1. Add item
        $addRes = $this->actingAs($admin, 'staff')->post('/backoffice/packing', [
            'category' => 'Bedding & Sleep',
            'item_name' => 'Fleece Sleeping Bag',
            'notes' => 'Rated for 10 degrees Celsius',
            'is_essential' => '1',
        ]);
        $addRes->assertSessionHas('success');

        $item = PackingList::where('item_name', 'Fleece Sleeping Bag')->first();
        $this->assertNotNull($item);
        $this->assertEquals('Bedding & Sleep', $item->category);
        $this->assertTrue((bool)$item->is_essential);

        // 2. Edit / Update item
        $editRes = $this->actingAs($admin, 'staff')->post("/backoffice/packing/{$item->id}/update", [
            'category' => 'Bedding & Sleep',
            'item_name' => 'Heavy Duty Thermal Sleeping Bag',
            'notes' => 'Label with permanent marker',
            'is_essential' => '1',
        ]);
        $editRes->assertSessionHas('success');

        $item->refresh();
        $this->assertEquals('Heavy Duty Thermal Sleeping Bag', $item->item_name);
        $this->assertEquals('Label with permanent marker', $item->notes);

        // 3. Remove / Delete item
        $delRes = $this->actingAs($admin, 'staff')->post("/backoffice/packing/{$item->id}/delete");
        $delRes->assertSessionHas('success');

        $this->assertDatabaseMissing('packing_lists', [
            'id' => $item->id,
        ]);
    }

    public function test_releasing_packing_list_dispatches_notifications_to_parents_and_teens(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $season = CampSeason::getActive();

        // Ensure packing list has items and is unreleased
        PackingList::where('camp_season_id', $season->id)->update(['is_released' => false]);

        $response = $this->actingAs($admin, 'staff')->post('/backoffice/packing/toggle-release');
        $response->assertSessionHas('success');

        // Check that packing items are now released
        $this->assertTrue(PackingList::where('camp_season_id', $season->id)->where('is_released', true)->exists());

        // Check that notifications were created
        $this->assertTrue(Notification::where('type', 'packing')->exists());
        $this->assertTrue(Notification::where('title', 'like', '%Packing Checklist%')->exists());
    }

    public function test_teen_and_parent_can_download_printable_packing_list_pdf(): void
    {
        $teen = User::where('role', 'teen')->first();
        $season = CampSeason::getActive();

        // Make sure packing list is released
        PackingList::where('camp_season_id', $season->id)->update(['is_released' => true]);

        // Teen accesses PDF checklist
        $response = $this->actingAs($teen, 'web')->get('/teen/packing-list/pdf');
        $response->assertStatus(200);
        $response->assertSee('Official Camper Packing', false);  // & is &amp; in HTML so match partial
        $response->assertSee('Preparation Checklist', false);
        $response->assertSee('Print / Download PDF Checklist', false);
        $response->assertSee($teen->name);
    }

    public function test_admin_can_update_camp_poster_and_core_values_and_view_on_landing(): void
    {
        $admin = User::where('email', 'admin@church.org')->first();
        $season = CampSeason::getActive();

        // Admin updates season with custom core values and fake poster
        $file = \Illuminate\Http\UploadedFile::fake()->create('custom_poster.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($admin, 'staff')->post("/backoffice/admin/seasons/{$season->id}", [
            'name' => $season->name,
            'year' => $season->year,
            'theme' => 'Radical Faith 2026',
            'start_date' => $season->start_date->format('Y-m-d H:i:s'),
            'end_date' => $season->end_date->format('Y-m-d H:i:s'),
            'venue' => $season->venue,
            'price' => $season->price,
            'capacity' => $season->capacity,
            'status' => 'active',
            'poster' => $file,
            'core_values' => [
                [
                    'title' => 'Unshakeable Holiness',
                    'icon' => 'bi-shield-shaded',
                    'description' => 'Walking in absolute purity and purpose throughout modern teenage life.',
                ],
                [
                    'title' => 'Kingdom Resilience',
                    'icon' => 'bi-lightning-charge-fill',
                    'description' => 'Standing bold in adversity through scripture memorization.',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Check season was updated
        $season->refresh();
        $this->assertNotNull($season->poster_path);
        $this->assertCount(2, $season->core_values);
        $this->assertEquals('Unshakeable Holiness', $season->core_values[0]['title']);

        // Check landing page reflects updated core values and poster
        $landingResponse = $this->get('/');
        $landingResponse->assertStatus(200);
        $landingResponse->assertSee('Unshakeable Holiness');
        $landingResponse->assertSee('Walking in absolute purity and purpose');
        $landingResponse->assertSee('Kingdom Resilience');
        $landingResponse->assertSee($season->poster_path);

        // Clean up uploaded test file
        if (file_exists(public_path($season->poster_path))) {
            @unlink(public_path($season->poster_path));
        }
    }
}



