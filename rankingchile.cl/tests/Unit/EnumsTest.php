<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Enums\GatewayProcessingResult;
use App\Enums\PaymentGateway;
use App\Enums\PaymentGatewayConfirmationStatus;
use App\Enums\PaymentGatewayDriver;
use App\Enums\ProfileClaimStatus;
use App\Enums\ProfileReportStatus;
use App\Enums\ProfileStatus;
use App\Enums\ProfileSubmissionStatus;
use App\Enums\ProfileType;
use App\Enums\RankingPeriodStatus;
use App\Enums\RankingPeriodType;
use App\Enums\SupportTransactionStatus;
use App\Enums\SupportTransactionType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use PHPUnit\Framework\Attributes\Test;

class EnumsTest extends \Tests\TestCase
{
    #[Test]
    public function currency_has_correct_value(): void
    {
        $this->assertSame('CLP', Currency::CLP->value);
        $this->assertCount(1, Currency::cases());
    }

    #[Test]
    public function payment_gateway_has_correct_value(): void
    {
        $this->assertSame('mercadopago', PaymentGateway::MercadoPago->value);
        $this->assertCount(1, PaymentGateway::cases());
    }

    #[Test]
    public function payment_gateway_driver_has_two_cases(): void
    {
        $this->assertSame('mercadopago', PaymentGatewayDriver::MercadoPago->value);
        $this->assertSame('local', PaymentGatewayDriver::Local->value);
        $this->assertCount(2, PaymentGatewayDriver::cases());
    }

    #[Test]
    public function payment_gateway_confirmation_status_has_three_cases(): void
    {
        $this->assertSame('approved', PaymentGatewayConfirmationStatus::Approved->value);
        $this->assertSame('pending', PaymentGatewayConfirmationStatus::Pending->value);
        $this->assertSame('failed', PaymentGatewayConfirmationStatus::Failed->value);
        $this->assertCount(3, PaymentGatewayConfirmationStatus::cases());
    }

    #[Test]
    public function gateway_processing_result_has_five_cases(): void
    {
        $this->assertSame('pending', GatewayProcessingResult::Pending->value);
        $this->assertSame('processed', GatewayProcessingResult::Processed->value);
        $this->assertSame('duplicate', GatewayProcessingResult::Duplicate->value);
        $this->assertSame('ignored', GatewayProcessingResult::Ignored->value);
        $this->assertSame('error', GatewayProcessingResult::Error->value);
        $this->assertCount(5, GatewayProcessingResult::cases());
    }

    #[Test]
    public function profile_status_has_five_cases(): void
    {
        $this->assertSame('pending_review', ProfileStatus::PendingReview->value);
        $this->assertSame('active', ProfileStatus::Active->value);
        $this->assertSame('suspended', ProfileStatus::Suspended->value);
        $this->assertSame('archived', ProfileStatus::Archived->value);
        $this->assertSame('rejected', ProfileStatus::Rejected->value);
        $this->assertCount(5, ProfileStatus::cases());
    }

    #[Test]
    public function profile_type_has_two_cases(): void
    {
        $this->assertSame('public_figure', ProfileType::PublicFigure->value);
        $this->assertSame('community', ProfileType::Community->value);
        $this->assertCount(2, ProfileType::cases());
    }

    #[Test]
    public function profile_submission_status_has_three_cases(): void
    {
        $this->assertSame('pending', ProfileSubmissionStatus::Pending->value);
        $this->assertSame('approved', ProfileSubmissionStatus::Approved->value);
        $this->assertSame('rejected', ProfileSubmissionStatus::Rejected->value);
        $this->assertCount(3, ProfileSubmissionStatus::cases());
    }

    #[Test]
    public function profile_report_status_has_four_cases(): void
    {
        $this->assertSame('open', ProfileReportStatus::Open->value);
        $this->assertSame('under_review', ProfileReportStatus::UnderReview->value);
        $this->assertSame('resolved', ProfileReportStatus::Resolved->value);
        $this->assertSame('rejected', ProfileReportStatus::Rejected->value);
        $this->assertCount(4, ProfileReportStatus::cases());
    }

    #[Test]
    public function profile_claim_status_has_three_cases(): void
    {
        $this->assertSame('pending', ProfileClaimStatus::Pending->value);
        $this->assertSame('verified', ProfileClaimStatus::Verified->value);
        $this->assertSame('rejected', ProfileClaimStatus::Rejected->value);
        $this->assertCount(3, ProfileClaimStatus::cases());
    }

    #[Test]
    public function profile_claim_and_submission_statuses_are_separate_domains(): void
    {
        $this->assertNull(ProfileSubmissionStatus::tryFrom(ProfileClaimStatus::Verified->value));
        $this->assertNull(ProfileClaimStatus::tryFrom(ProfileSubmissionStatus::Approved->value));
        $this->assertNotSame(ProfileSubmissionStatus::class, ProfileClaimStatus::class);
    }

    #[Test]
    public function verification_status_has_three_cases(): void
    {
        $this->assertSame('unverified', VerificationStatus::Unverified->value);
        $this->assertSame('pending', VerificationStatus::Pending->value);
        $this->assertSame('verified', VerificationStatus::Verified->value);
        $this->assertCount(3, VerificationStatus::cases());
    }

    #[Test]
    public function support_transaction_status_has_six_cases(): void
    {
        $this->assertSame('pending', SupportTransactionStatus::Pending->value);
        $this->assertSame('approved', SupportTransactionStatus::Approved->value);
        $this->assertSame('failed', SupportTransactionStatus::Failed->value);
        $this->assertSame('disputed', SupportTransactionStatus::Disputed->value);
        $this->assertSame('refunded', SupportTransactionStatus::Refunded->value);
        $this->assertSame('reversed', SupportTransactionStatus::Reversed->value);
        $this->assertCount(6, SupportTransactionStatus::cases());
    }

    #[Test]
    public function support_transaction_type_has_two_cases(): void
    {
        $this->assertSame('real', SupportTransactionType::Real->value);
        $this->assertSame('promotional', SupportTransactionType::Promotional->value);
        $this->assertCount(2, SupportTransactionType::cases());
    }

    #[Test]
    public function ranking_period_type_has_seven_cases(): void
    {
        $this->assertSame('daily', RankingPeriodType::Daily->value);
        $this->assertSame('weekly', RankingPeriodType::Weekly->value);
        $this->assertSame('monthly', RankingPeriodType::Monthly->value);
        $this->assertSame('quarterly', RankingPeriodType::Quarterly->value);
        $this->assertSame('semester', RankingPeriodType::Semester->value);
        $this->assertSame('yearly', RankingPeriodType::Yearly->value);
        $this->assertSame('custom', RankingPeriodType::Custom->value);
        $this->assertCount(7, RankingPeriodType::cases());
    }

    #[Test]
    public function ranking_period_status_has_seven_cases(): void
    {
        $this->assertSame('draft', RankingPeriodStatus::Draft->value);
        $this->assertSame('scheduled', RankingPeriodStatus::Scheduled->value);
        $this->assertSame('active', RankingPeriodStatus::Active->value);
        $this->assertSame('closed_pending_settlement', RankingPeriodStatus::ClosedPendingSettlement->value);
        $this->assertSame('closed', RankingPeriodStatus::Closed->value);
        $this->assertSame('snapshotted', RankingPeriodStatus::Snapshotted->value);
        $this->assertSame('cancelled', RankingPeriodStatus::Cancelled->value);
        $this->assertCount(7, RankingPeriodStatus::cases());
    }

    #[Test]
    public function ranking_period_status_state_machine_transitions(): void
    {
        // Draft -> Scheduled, Cancelled
        $this->assertTrue(RankingPeriodStatus::Draft->canTransitionTo(RankingPeriodStatus::Scheduled));
        $this->assertTrue(RankingPeriodStatus::Draft->canTransitionTo(RankingPeriodStatus::Cancelled));
        $this->assertFalse(RankingPeriodStatus::Draft->canTransitionTo(RankingPeriodStatus::Active));

        // Scheduled -> Active, Cancelled
        $this->assertTrue(RankingPeriodStatus::Scheduled->canTransitionTo(RankingPeriodStatus::Active));
        $this->assertTrue(RankingPeriodStatus::Scheduled->canTransitionTo(RankingPeriodStatus::Cancelled));
        $this->assertFalse(RankingPeriodStatus::Scheduled->canTransitionTo(RankingPeriodStatus::Draft));

        // Active -> ClosedPendingSettlement
        $this->assertTrue(RankingPeriodStatus::Active->canTransitionTo(RankingPeriodStatus::ClosedPendingSettlement));
        $this->assertFalse(RankingPeriodStatus::Active->canTransitionTo(RankingPeriodStatus::Closed));
        $this->assertFalse(RankingPeriodStatus::Active->canTransitionTo(RankingPeriodStatus::Cancelled));

        // ClosedPendingSettlement -> Closed
        $this->assertTrue(RankingPeriodStatus::ClosedPendingSettlement->canTransitionTo(RankingPeriodStatus::Closed));
        $this->assertFalse(RankingPeriodStatus::ClosedPendingSettlement->canTransitionTo(RankingPeriodStatus::Active));

        // Closed -> Snapshotted
        $this->assertTrue(RankingPeriodStatus::Closed->canTransitionTo(RankingPeriodStatus::Snapshotted));
        $this->assertFalse(RankingPeriodStatus::Closed->canTransitionTo(RankingPeriodStatus::Active));

        // Snapshotted -> nothing
        $this->assertFalse(RankingPeriodStatus::Snapshotted->canTransitionTo(RankingPeriodStatus::Active));
        $this->assertFalse(RankingPeriodStatus::Snapshotted->canTransitionTo(RankingPeriodStatus::Closed));

        // Cancelled -> nothing
        $this->assertFalse(RankingPeriodStatus::Cancelled->canTransitionTo(RankingPeriodStatus::Active));
        $this->assertFalse(RankingPeriodStatus::Cancelled->canTransitionTo(RankingPeriodStatus::Draft));
    }

    #[Test]
    public function user_role_has_four_cases(): void
    {
        $this->assertSame('admin', UserRole::Admin->value);
        $this->assertSame('super_admin', UserRole::SuperAdmin->value);
        $this->assertSame('moderator', UserRole::Moderator->value);
        $this->assertSame('finance_reviewer', UserRole::FinanceReviewer->value);
        $this->assertCount(4, UserRole::cases());
    }

    #[Test]
    public function user_status_has_two_cases(): void
    {
        $this->assertSame('active', UserStatus::Active->value);
        $this->assertSame('suspended', UserStatus::Suspended->value);
        $this->assertCount(2, UserStatus::cases());
    }

    #[Test]
    public function enums_are_backed_by_strings(): void
    {
        $enums = [
            Currency::class, PaymentGateway::class, PaymentGatewayDriver::class,
            PaymentGatewayConfirmationStatus::class, GatewayProcessingResult::class,
            ProfileStatus::class, ProfileType::class, ProfileSubmissionStatus::class,
            ProfileReportStatus::class, ProfileClaimStatus::class, VerificationStatus::class,
            SupportTransactionStatus::class, SupportTransactionType::class,
            RankingPeriodType::class, RankingPeriodStatus::class,
            UserRole::class, UserStatus::class,
        ];

        foreach ($enums as $enum) {
            $this->assertTrue(
                enum_exists($enum),
                "{$enum} should be a valid enum"
            );
            foreach ($enum::cases() as $case) {
                $this->assertIsString($case->value, "{$enum}::{$case->name} should have string value");
            }
        }
    }
}
