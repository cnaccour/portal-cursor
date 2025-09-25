<?php
require __DIR__.'/../includes/auth.php';
// Forms are accessible to all users - no login required

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		require_once __DIR__.'/../includes/db.php';

		// Collect form data
		$first_name = trim($_POST['first_name'] ?? '');
		$last_name = trim($_POST['last_name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$work_location = $_POST['work_location'] ?? '';
		$role = trim($_POST['role'] ?? 'Educator');
		$date_range = $_POST['date_range'] ?? '';

		// New fieldset per specifications
		$apprentice_first_name = trim($_POST['apprentice_first_name'] ?? '');
		$apprentice_last_name = trim($_POST['apprentice_last_name'] ?? '');
		$educator_name = trim(($first_name ?? '') . ' ' . ($last_name ?? ''));
		$apprentice_name = trim($apprentice_first_name . ' ' . $apprentice_last_name);
		$understands_4rs = ($_POST['understands_4rs'] ?? '') === 'yes' ? 1 : 0;
		$four_rs_notes = trim($_POST['four_rs_notes'] ?? '');
		$on_time_prepared_engaged = ($_POST['on_time_prepared_engaged'] ?? '') === 'yes' ? 1 : 0;
		$on_time_prepared_engaged_notes = trim($_POST['on_time_prepared_engaged_notes'] ?? '');
		$needs_focus_on = trim($_POST['needs_focus_on'] ?? '');
		$units_completed_or_help = $_POST['units_completed_or_help'] ?? '';
		$units_completed_or_help_notes = trim($_POST['units_completed_or_help_notes'] ?? '');
		$practicing_and_asking = ($_POST['practicing_and_asking'] ?? '') === 'yes' ? 1 : 0;
		$practicing_and_asking_notes = trim($_POST['practicing_and_asking_notes'] ?? '');
		$would_work_at_location = ($_POST['would_work_at_location'] ?? '') === 'yes' ? 1 : 0;
		$would_work_at_location_notes = trim($_POST['would_work_at_location_notes'] ?? '');
		$stage_success_rating = (int)($_POST['stage_success_rating'] ?? 0); // 1..5
		$retail_conversation_followup = ($_POST['retail_conversation_followup'] ?? '') === 'yes' ? 1 : 0;
		$retail_conversation_followup_notes = trim($_POST['retail_conversation_followup_notes'] ?? '');
		$guest_feedback = trim($_POST['guest_feedback'] ?? '');
		$finishing_rating = (int)($_POST['finishing_rating'] ?? 0); // 1..5
		$finishing_helping_notes = trim($_POST['finishing_helping_notes'] ?? '');
		$loyalty_score = (int)($_POST['loyalty_score'] ?? 0);
		$long_term_commitment_score = (int)($_POST['long_term_commitment_score'] ?? 0);
		$adaptability_score = (int)($_POST['adaptability_score'] ?? 0);
		$interpersonal_skills_score = (int)($_POST['interpersonal_skills_score'] ?? 0);
		$professional_growth_score = (int)($_POST['professional_growth_score'] ?? 0);
		$remarks = trim($_POST['remarks'] ?? '');
		$additional_info = trim($_POST['additional_info'] ?? '');
		$submitted_by = $_SESSION['user_id'] ?? null;

		// Parse date range (format: "YYYY-MM-DD to YYYY-MM-DD")
		$period_start_date = '';
		$period_end_date = '';
		if ($date_range && strpos($date_range, ' to ') !== false) {
			list($period_start_date, $period_end_date) = explode(' to ', $date_range);
		}

        // Map names for legacy listings (used in admin submissions header)
        if (empty($first_name) && !empty($educator_name)) { $first_name = $educator_name; }
        if (empty($last_name) && !empty($apprentice_name)) { $last_name = $apprentice_name; }

        // Basic validation
        $errors = [];
		if (empty($email)) $errors[] = 'Email is required';
		if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address';
        if (empty($educator_name)) $errors[] = 'Educator name is required';
        if (empty($apprentice_name)) $errors[] = "Apprentice's name is required";
		if (empty($date_range)) $errors[] = 'Reporting period (date range) is required';
		if ($stage_success_rating < 1 || $stage_success_rating > 5) $errors[] = 'Please rate current stage success (1-5)';
        // Knowledge & Preparedness mandatory validations
        $understands_val = $_POST['understands_4rs'] ?? '';
        if (!in_array($understands_val, ['yes','no'], true)) {
            $errors[] = "Please select whether the apprentice understands the 4R's";
        }
        if ($understands_val === 'no' && trim($four_rs_notes) === '') {
            $errors[] = "Please elaborate on the 4R's so the next educator can assist";
        }
        if (!in_array($_POST['on_time_prepared_engaged'] ?? '', ['yes','no'], true)) {
            $errors[] = 'Please confirm on-time, prepared and engaged';
        }
        if (($_POST['on_time_prepared_engaged'] ?? '') === 'no' && trim($on_time_prepared_engaged_notes) === '') {
            $errors[] = 'Please elaborate on on-time/prepared/engaged so the next educator can assist';
        }
        if (!in_array($_POST['units_completed_or_help'] ?? '', ['completed','asked_help'], true)) {
            $errors[] = 'Please select whether units were completed or help was needed';
        }
        if (($_POST['units_completed_or_help'] ?? '') === 'asked_help' && trim($units_completed_or_help_notes) === '') {
            $errors[] = 'Please elaborate on units completed/help so the next educator can assist';
        }
        if (!in_array($_POST['practicing_and_asking'] ?? '', ['yes','no'], true)) {
            $errors[] = 'Please confirm practicing and asking during downtime';
        }
        if (($_POST['practicing_and_asking'] ?? '') === 'no' && trim($practicing_and_asking_notes) === '') {
            $errors[] = 'Please elaborate on practicing/asking so the next educator can assist';
        }
        if (empty($needs_focus_on)) {
            $errors[] = 'Please provide what the apprentice should focus on more';
        }
        if (!in_array(($would_work_at_location ? 'yes' : ($_POST['would_work_at_location'] ?? '')), ['yes','no'], true)) {
            $errors[] = 'Please confirm if you would want the apprentice at your location';
        }
        if ((($_POST['would_work_at_location'] ?? '') === 'no') && trim($would_work_at_location_notes) === '') {
            $errors[] = 'Please elaborate why you would not want the apprentice at your location';
        }

		// Retail & Guest Feedback mandatory validations
		if (!in_array(($_POST['finishing_helping'] ?? ''), ['yes','no'], true)) {
			$errors[] = 'Please confirm if the apprentice is helping with finishing';
		}
		if (!in_array(($_POST['retail_conversation_followup'] ?? ''), ['yes','no'], true)) {
			$errors[] = 'Please confirm retail conversation and follow-up at checkout';
		}
        if ((($_POST['retail_conversation_followup'] ?? '') === 'no') && trim($retail_conversation_followup_notes) === '') {
            $errors[] = 'Please elaborate on retail conversation/follow-up so the next educator can assist';
        }
		if ($guest_feedback === '') {
			$errors[] = "Please enter your guest’s feedback about the apprentice";
		}
		if (($_POST['finishing_helping'] ?? '') === 'yes') {
			if ($finishing_rating < 1 || $finishing_rating > 5) {
				$errors[] = 'Please rate finishing work (1-5)';
			}
        } else if (($_POST['finishing_helping'] ?? '') === 'no') {
            if (trim($finishing_helping_notes) === '') {
                $errors[] = 'Please elaborate on finishing help so the next educator can assist';
            }
		}

		// Manager Evaluation Scales mandatory validations
		if ($loyalty_score < 1 || $loyalty_score > 5) {
			$errors[] = 'Please rate Loyalty (1-5)';
		}
		if ($long_term_commitment_score < 1 || $long_term_commitment_score > 5) {
			$errors[] = 'Please rate Long-term Commitment (1-5)';
		}
		if ($adaptability_score < 1 || $adaptability_score > 5) {
			$errors[] = 'Please rate Adaptability (1-5)';
		}
		if ($interpersonal_skills_score < 1 || $interpersonal_skills_score > 5) {
			$errors[] = 'Please rate Interpersonal Skills (1-5)';
		}
		if ($professional_growth_score < 1 || $professional_growth_score > 5) {
			$errors[] = 'Please rate Professional Growth (1-5)';
		}

		// Validate dates
		if (!empty($period_start_date) && !empty($period_end_date)) {
			$start = strtotime($period_start_date);
			$end = strtotime($period_end_date);
			if ($start > $end) {
				$errors[] = 'End date must be after start date';
			}
		}

		if (empty($errors)) {
			// Create table if it doesn't exist
			$pdo->exec("\n\t\t\t\tCREATE TABLE IF NOT EXISTS bi_weekly_reports (\n\t\t\t\tid INT AUTO_INCREMENT PRIMARY KEY,\n\t\t\t\tfirst_name VARCHAR(255) NOT NULL,\n\t\t\t\tlast_name VARCHAR(255) NOT NULL,\n\t\t\t\temail VARCHAR(255) NOT NULL,\n\t\t\t\twork_location VARCHAR(100) NOT NULL,\n\t\t\t\trole VARCHAR(150) NOT NULL,\n\t\t\t\tperiod_start_date DATE NOT NULL,\n\t\t\t\tperiod_end_date DATE NOT NULL,\n\t\t\t\tdate_range VARCHAR(255),\n\t\t\t\teducator_name VARCHAR(255),\n\t\t\t\tapprentice_name VARCHAR(255),\n\t\t\t\tunderstands_4rs TINYINT(1) DEFAULT 0,\n\t\t\t\tfour_rs_notes TEXT,\n\t\t\t\ton_time_prepared_engaged TINYINT(1) DEFAULT 0,\n\t\t\t\ton_time_prepared_engaged_notes TEXT,\n\t\t\t\tneeds_focus_on TEXT,\n\t\t\t\tunits_completed_or_help VARCHAR(50),\n\t\t\t\tunits_completed_or_help_notes TEXT,\n\t\t\t\tpracticing_and_asking TINYINT(1) DEFAULT 0,\n\t\t\t\tpracticing_and_asking_notes TEXT,\n\t\t\t\twould_work_at_location TINYINT(1) DEFAULT 0,\n\t\t\t\tstage_success_rating TINYINT DEFAULT 0,\n\t\t\t\tretail_conversation_followup TINYINT(1) DEFAULT 0,\n\t\t\t\tguest_feedback TEXT,\n\t\t\t\tfinishing_rating TINYINT DEFAULT 0,\n\t\t\t\tloyalty_score TINYINT DEFAULT 0,\n\t\t\t\tlong_term_commitment_score TINYINT DEFAULT 0,\n\t\t\t\tadaptability_score TINYINT DEFAULT 0,\n\t\t\t\tinterpersonal_skills_score TINYINT DEFAULT 0,\n\t\t\t\tprofessional_growth_score TINYINT DEFAULT 0,\n\t\t\t\tremarks TEXT,\n\t\t\t\tadditional_info TEXT,\n\t\t\t\tstatus VARCHAR(50) DEFAULT 'submitted',\n\t\t\t\tsubmitted_by INT NULL,\n\t\t\t\tsubmitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n\t\t\t) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\t\t");

			// Insert submission
			$stmt = $pdo->prepare("\n\t\t\t\tINSERT INTO bi_weekly_reports\n\t\t\t\t(first_name, last_name, email, work_location, role, period_start_date, period_end_date, date_range,\n\t\t\t\teducator_name, apprentice_name, understands_4rs, four_rs_notes, on_time_prepared_engaged, on_time_prepared_engaged_notes, needs_focus_on, units_completed_or_help, units_completed_or_help_notes, practicing_and_asking, practicing_and_asking_notes, would_work_at_location, stage_success_rating, retail_conversation_followup, guest_feedback, finishing_rating, loyalty_score, long_term_commitment_score, adaptability_score, interpersonal_skills_score, professional_growth_score, remarks, additional_info, submitted_by)\n\t\t\t\tVALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)\n\t\t\t");
			$stmt->execute([
				$first_name, $last_name, $email, $work_location, $role,
				$period_start_date, $period_end_date, $date_range,
				$educator_name, $apprentice_name, $understands_4rs, $four_rs_notes, $on_time_prepared_engaged, $on_time_prepared_engaged_notes, $needs_focus_on, $units_completed_or_help, $units_completed_or_help_notes, $practicing_and_asking, $practicing_and_asking_notes, $would_work_at_location, $stage_success_rating, $retail_conversation_followup, $guest_feedback, $finishing_rating,
				$loyalty_score, $long_term_commitment_score, $adaptability_score, $interpersonal_skills_score, $professional_growth_score, $remarks, $additional_info, $submitted_by
			]);

			// Notifications
			try {
				require_once __DIR__.'/../includes/email-notifications.php';
				$config_stmt = $pdo->prepare("SELECT notification_emails FROM forms_config WHERE form_key = ?");
				$config_stmt->execute(['bi_weekly_report']);
				$config_result = $config_stmt->fetch(PDO::FETCH_ASSOC);
				if ($config_result && !empty($config_result['notification_emails'])) {
					$notification_emails = json_decode($config_result['notification_emails'], true);
					if (is_array($notification_emails) && !empty($notification_emails)) {
						$submission_data = [
							'educator_name' => $educator_name,
							'apprentice_name' => $apprentice_name,
							'work_location' => $work_location,
							'date_range' => $date_range,
							'understands_4rs' => $understands_4rs ? 'Yes' : 'No',
							'needs_focus_on' => $needs_focus_on,
							'practicing_and_asking' => $practicing_and_asking ? 'Yes' : 'No',
							'location_preference' => $would_work_at_location ? 'Yes' : 'No',
							'stage_success_rating' => $stage_success_rating,
							'finishing_rating' => $finishing_rating,
							'loyalty_score' => $loyalty_score,
							'long_term_commitment_score' => $long_term_commitment_score,
							'adaptability_score' => $adaptability_score,
							'interpersonal_skills_score' => $interpersonal_skills_score,
							'professional_growth_score' => $professional_growth_score
						];
						// Reuse existing email template structure for now
						EmailNotifications::sendGenericNotification('Bi-Weekly Report Submitted', $submission_data, $notification_emails);
					}
				}
			} catch (Exception $email_error) {
				error_log('Email notification error: ' . $email_error->getMessage());
			}

			$success = "Your bi-weekly report has been submitted successfully. Thank you!";
		}

	} catch (Exception $e) {
		error_log('Bi-Weekly Report error: ' . $e->getMessage());
		$errors[] = 'An error occurred while submitting your report. Please try again.';
	}
}

require __DIR__.'/../includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
.form-field {
	transition: all 0.2s ease;
}
.form-field:focus {
	outline: none;
	border-color: #AF831A;
	box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}
.checkbox-custom { width: 18px; height: 18px; accent-color: #AF831A; }
.section-card { background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 24px; }
.help { color: #6B7280; font-size: 12px; }
.badge { display:inline-block; padding: 4px 8px; background: #FFFBF0; color: #92400E; border:1px solid #F2D28E; border-radius: 6px; font-size: 12px; }
</style>

<h1 class="text-2xl font-semibold mb-6">Bi-Weekly Report</h1>

<?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-green-800 font-medium"><?= htmlspecialchars($success) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="font-medium text-red-800 mb-2">Please correct the following errors:</div>
        <ul class="text-red-700 text-sm space-y-1">
            <?php foreach ($errors as $error): ?>
                <li>• <?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="space-y-6" x-data="biWeeklyForm()">

    <section class="section-card">
        <h2 class="text-lg font-semibold mb-4">Educator Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">First Name <span style="color:#AF831A;">*</span></label>
                <input type="text" name="first_name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" placeholder="Enter educator first name" class="w-full border rounded-lg px-3 py-2 form-field">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Last Name <span style="color:#AF831A;">*</span></label>
                <input type="text" name="last_name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" placeholder="Enter educator last name" class="w-full border rounded-lg px-3 py-2 form-field">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Email <span style="color:#AF831A;">*</span></label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Educator email address" class="w-full border rounded-lg px-3 py-2 form-field">
            </div>
        </div>
    </section>

    <section class="section-card">
        <h2 class="text-lg font-semibold mb-4">Apprentice Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">First Name <span style="color:#AF831A;">*</span></label>
                <input type="text" name="apprentice_first_name" value="<?= htmlspecialchars($_POST['apprentice_first_name'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2 form-field" placeholder="Enter apprentice first name">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Last Name <span style="color:#AF831A;">*</span></label>
                <input type="text" name="apprentice_last_name" value="<?= htmlspecialchars($_POST['apprentice_last_name'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2 form-field" placeholder="Enter apprentice last name">
            </div>
        </div>
    </section>

	<section class="section-card">
		<h2 class="text-lg font-semibold mb-4">Work Details</h2>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
			<div>
				<label class="block text-sm font-medium mb-2">Work Location <span style="color:#AF831A;">*</span></label>
				<select name="work_location" required class="w-full border rounded-lg px-3 py-2 form-field">
					<option value="">Select location...</option>
					<option value="land-o-lakes" <?= (($_POST['work_location'] ?? '')==='land-o-lakes')?'selected':''; ?>>Land O' Lakes</option>
					<option value="lutz" <?= (($_POST['work_location'] ?? '')==='lutz')?'selected':''; ?>>Lutz</option>
					<option value="citrus-park" <?= (($_POST['work_location'] ?? '')==='citrus-park')?'selected':''; ?>>Citrus Park</option>
					<option value="odessa" <?= (($_POST['work_location'] ?? '')==='odessa')?'selected':''; ?>>Odessa</option>
					<option value="wesley-chapel" <?= (($_POST['work_location'] ?? '')==='wesley-chapel')?'selected':''; ?>>Wesley Chapel</option>
				</select>
			</div>
			<div>
				<label class="block text-sm font-medium mb-2">Role <span style="color:#AF831A;">*</span></label>
				<input type="text" name="role" required value="<?= htmlspecialchars($_POST['role'] ?? '') ?>" placeholder="Stylist, Assistant, FD Manager, ..." class="w-full border rounded-lg px-3 py-2 form-field">
			</div>
			
		</div>
	</section>

	<section class="section-card">
		<h2 class="text-lg font-semibold mb-4">Reporting Period</h2>
		<div>
			<label class="block text-sm font-medium mb-2">Select Date Range <span style="color:#AF831A;">*</span></label>
			<input type="text" id="dateRangePicker" name="date_range" required placeholder="Choose date range..." value="<?= htmlspecialchars($_POST['date_range'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 form-field" readonly>
			<div class="mt-2"><span class="badge" x-show="dayCount>0" x-text="formatDateRange() + ' • ' + (dayCount===1? '1 day' : dayCount + ' days')"></span></div>
		</div>
	</section>

    <section class="section-card" x-data="{ understands4rs: '<?= (($_POST['understands_4rs'] ?? 'yes') === 'no') ? 'no' : 'yes' ?>' }">
        <h2 class="text-lg font-semibold mb-4">Knowledge & Preparedness</h2>
        <div class="grid grid-cols-1 gap-4">
            <div class="col-span-1 md:col-span-1">
                <label class="block text-sm font-medium mb-2">Understands 4R's <span style="color:#AF831A;">*</span></label>
                <div class="flex items-center gap-4">
                    <?php $u4rs = $_POST['understands_4rs'] ?? 'yes'; $u4rs_yes = ($u4rs==='yes') ? 'checked' : ''; $u4rs_no = ($u4rs==='no') ? 'checked' : ''; ?>
                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="understands_4rs" value="yes" class="checkbox-custom" @change="understands4rs='yes'" <?= $u4rs_yes ?>> <span class="text-sm">Yes</span></label>
                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="understands_4rs" value="no" class="checkbox-custom" @change="understands4rs='no'" <?= $u4rs_no ?>> <span class="text-sm">No</span></label>
                </div>
                <div class="mt-3" x-show="understands4rs==='no'" x-transition>
                    <label class="block text-sm font-medium mb-2">Please elaborate so that the next educator can work with them on it: <span style="color:#AF831A;">*</span></label>
                    <textarea name="four_rs_notes" rows="3" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Add details to help the next educator..."><?= htmlspecialchars($_POST['four_rs_notes'] ?? '') ?></textarea>
                </div>
            </div>
            <div x-data="{ v: '<?= (($_POST['on_time_prepared_engaged'] ?? 'yes') === 'no') ? 'no' : 'yes' ?>' }">
                <label class="block text-sm font-medium mb-2">On time, prepared, and engaged? <span style="color:#AF831A;">*</span></label>
                <div class="flex items-center gap-4">
                    <?php $otpe = $_POST['on_time_prepared_engaged'] ?? 'yes'; $otpe_yes = ($otpe==='yes') ? 'checked' : ''; $otpe_no = ($otpe==='no') ? 'checked' : ''; ?>
                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="on_time_prepared_engaged" value="yes" class="checkbox-custom" @change="v='yes'" <?= $otpe_yes ?>> <span class="text-sm">Yes</span></label>
                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="on_time_prepared_engaged" value="no" class="checkbox-custom" @change="v='no'" <?= $otpe_no ?>> <span class="text-sm">No</span></label>
                </div>
                <div class="mt-3" x-show="v==='no'" x-transition>
                    <label class="block text-sm font-medium mb-2">Please elaborate so that the next educator can work with them on it: <span style="color:#AF831A;">*</span></label>
                    <textarea name="on_time_prepared_engaged_notes" rows="3" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Add details..."><?php echo htmlspecialchars($_POST['on_time_prepared_engaged_notes'] ?? ''); ?></textarea>
                </div>
            </div>
            <div x-data="{ v: '<?= (($_POST['units_completed_or_help'] ?? 'completed') === 'asked_help') ? 'asked_help' : 'completed' ?>' }">
                <label class="block text-sm font-medium mb-2">Units completed or needed help? <span style="color:#AF831A;">*</span></label>
                <div class="flex items-center gap-4">
                    <?php $uhelp = $_POST['units_completed_or_help'] ?? 'completed'; $uhelp_c = ($uhelp==='completed') ? 'checked' : ''; $uhelp_h = ($uhelp==='asked_help') ? 'checked' : ''; ?>
                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="units_completed_or_help" value="completed" class="checkbox-custom" @change="v='completed'" <?= $uhelp_c ?>> <span class="text-sm">Completed</span></label>
                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="units_completed_or_help" value="asked_help" class="checkbox-custom" @change="v='asked_help'" <?= $uhelp_h ?>> <span class="text-sm">Asked for help</span></label>
                </div>
                <div class="mt-3" x-show="v==='asked_help'" x-transition>
                    <label class="block text-sm font-medium mb-2">Please elaborate so that the next educator can work with them on it: <span style="color:#AF831A;">*</span></label>
                    <textarea name="units_completed_or_help_notes" rows="3" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Add details..."><?php echo htmlspecialchars($_POST['units_completed_or_help_notes'] ?? ''); ?></textarea>
                </div>
            </div>
            <div x-data="{ v: '<?= (($_POST['practicing_and_asking'] ?? 'yes') === 'no') ? 'no' : 'yes' ?>' }">
                <label class="block text-sm font-medium mb-2">Practicing and asking questions during downtime? <span style="color:#AF831A;">*</span></label>
                <div class="flex items-center gap-4">
                    <?php $paa = $_POST['practicing_and_asking'] ?? 'yes'; $paa_yes = ($paa==='yes') ? 'checked' : ''; $paa_no = ($paa==='no') ? 'checked' : ''; ?>
                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="practicing_and_asking" value="yes" class="checkbox-custom" @change="v='yes'" <?= $paa_yes ?>> <span class="text-sm">Yes</span></label>
                    <label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="practicing_and_asking" value="no" class="checkbox-custom" @change="v='no'" <?= $paa_no ?>> <span class="text-sm">No</span></label>
                </div>
                <div class="mt-3" x-show="v==='no'" x-transition>
                    <label class="block text-sm font-medium mb-2">Please elaborate so that the next educator can work with them on it: <span style="color:#AF831A;">*</span></label>
                    <textarea name="practicing_and_asking_notes" rows="3" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Add details..."><?php echo htmlspecialchars($_POST['practicing_and_asking_notes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium mb-2">What should the apprentice focus on more? <span style="color:#AF831A;">*</span></label>
            <textarea name="needs_focus_on" rows="3" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Focus areas..."><?= htmlspecialchars($_POST['needs_focus_on'] ?? '') ?></textarea>
        </div>
    </section>

	<section class="section-card">
		<h2 class="text-lg font-semibold mb-4">Placement & Stage Rating</h2>
		<div class="grid grid-cols-1 gap-4">
            <div x-data="{ v: '<?= (($_POST['would_work_at_location'] ?? 'yes') === 'no') ? 'no' : 'yes' ?>' }">
                <label class="block text-sm font-medium mb-2">Would you want the apprentice working at your location? <span style="color:#AF831A;">*</span></label>
				<div class="flex items-center gap-4">
                    <?php $wwl = $_POST['would_work_at_location'] ?? 'yes'; $wwl_yes = ($wwl==='yes') ? 'checked' : ''; $wwl_no = ($wwl==='no') ? 'checked' : ''; ?>
					<label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="would_work_at_location" value="yes" class="checkbox-custom" @change="v='yes'" <?= $wwl_yes ?>> <span class="text-sm">Yes</span></label>
					<label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="would_work_at_location" value="no" class="checkbox-custom" @change="v='no'" <?= $wwl_no ?>> <span class="text-sm">No</span></label>
				</div>
				<div class="mt-3" x-show="v==='no'" x-transition>
					<label class="block text-sm font-medium mb-2">Please elaborate so that the next educator can work with them on it: <span style="color:#AF831A;">*</span></label>
					<textarea name="would_work_at_location_notes" rows="3" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Add details..."><?php echo htmlspecialchars($_POST['would_work_at_location_notes'] ?? ''); ?></textarea>
				</div>
			</div>
			<div>
                <label class="block text-sm font-medium mb-2">At this current stage of the apprentice program, rate how successful they would be when getting on the floor <span style="color:#AF831A;">*</span></label>
				<div class="grid grid-cols-5 gap-2 justify-items-start">
					<?php $labels = ['Very poor','Poor','Neutral','Good','Excellent']; for($i=1;$i<=5;$i++): $checked = ((int)($_POST['stage_success_rating'] ?? 0) === $i) ? 'checked' : ''; ?>
					<label class="text-center cursor-pointer">
						<input type="radio" name="stage_success_rating" value="<?= $i ?>" <?= $checked ?> class="checkbox-custom">
						<div class="text-[11px] mt-1"><?= $labels[$i-1] ?></div>
					</label>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="section-card">
		<h2 class="text-lg font-semibold mb-4">Retail & Guest Feedback</h2>
		<div class="grid grid-cols-1 gap-4">
			<div x-data="{ v: '<?= (($_POST['retail_conversation_followup'] ?? 'yes') === 'no') ? 'no' : 'yes' ?>' }">
				<label class="block text-sm font-medium mb-2">Is your apprentice having a conversation with guests about the products and shampoo being used on them, and following up at checkout? <span style="color:#AF831A;">*</span></label>
				<div class="flex items-center gap-4">
					<?php $rcf = $_POST['retail_conversation_followup'] ?? 'yes'; $rcf_yes = ($rcf==='yes') ? 'checked' : ''; $rcf_no = ($rcf==='no') ? 'checked' : ''; ?>
					<label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="retail_conversation_followup" value="yes" class="checkbox-custom" @change="v='yes'" <?= $rcf_yes ?>> <span class="text-sm">Yes</span></label>
					<label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="retail_conversation_followup" value="no" class="checkbox-custom" @change="v='no'" <?= $rcf_no ?>> <span class="text-sm">No</span></label>
				</div>
				<div class="mt-3" x-show="v==='no'" x-transition>
					<label class="block text-sm font-medium mb-2">Please elaborate so that the next educator can work with them on it: <span style="color:#AF831A;">*</span></label>
					<textarea name="retail_conversation_followup_notes" rows="3" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Add details..."><?php echo htmlspecialchars($_POST['retail_conversation_followup_notes'] ?? ''); ?></textarea>
				</div>
			</div>
			<div>
				<label class="block text-sm font-medium mb-2">What is your guest’s feedback about your current apprentice? <span style="color:#AF831A;">*</span></label>
				<textarea name="guest_feedback" rows="4" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Guest feedback..."><?= htmlspecialchars($_POST['guest_feedback'] ?? '') ?></textarea>
			</div>
			<div x-data="{ v: '<?= (($_POST['finishing_helping'] ?? 'no') === 'yes') ? 'yes' : 'no' ?>' }">
				<label class="block text-sm font-medium mb-2">Is this apprentice helping you with your finishing? <span style="color:#AF831A;">*</span></label>
				<div class="flex items-center gap-4">
					<?php $fh = $_POST['finishing_helping'] ?? 'no'; $fh_yes = ($fh==='yes') ? 'checked' : ''; $fh_no = ($fh!=='yes') ? 'checked' : ''; ?>
					<label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="finishing_helping" value="yes" class="checkbox-custom" @change="v='yes'" <?= $fh_yes ?>> <span class="text-sm">Yes</span></label>
					<label class="inline-flex items-center gap-2 cursor-pointer"><input type="radio" name="finishing_helping" value="no" class="checkbox-custom" @change="v='no'" <?= $fh_no ?>> <span class="text-sm">No</span></label>
				</div>
				<div class="mt-3" x-show="v==='yes'" x-transition>
					<label class="block text-sm font-medium mb-2">How would you rate their finishing work? <span style="color:#AF831A;">*</span></label>
					<div class="grid grid-cols-5 gap-2 justify-items-start">
						<?php $labels2 = ['Very poor','Poor','Neutral','Good','Excellent']; for($i=1;$i<=5;$i++): $checked = ((int)($_POST['finishing_rating'] ?? 0) === $i) ? 'checked' : ''; ?>
						<label class="text-center cursor-pointer">
							<input type="radio" name="finishing_rating" value="<?= $i ?>" <?= $checked ?> class="checkbox-custom">
							<div class="text-[11px] mt-1"><?= $labels2[$i-1] ?></div>
						</label>
						<?php endfor; ?>
					</div>
				</div>
				<div class="mt-3" x-show="v==='no'" x-transition>
					<label class="block text-sm font-medium mb-2">Please elaborate so that the next educator can work with them on it: <span style="color:#AF831A;">*</span></label>
					<textarea name="finishing_helping_notes" rows="3" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Add details..."><?php echo htmlspecialchars($_POST['finishing_helping_notes'] ?? ''); ?></textarea>
				</div>
			</div>
		</div>
	</section>

	<section class="section-card">
		<h2 class="text-lg font-semibold mb-4">Manager Evaluation Scales (1–5)</h2>
		<p class="text-sm text-gray-600 mb-4">Managers should consider these criteria and ratings when evaluating apprentices. By assessing their loyalty, long-term commitment, adaptability, interpersonal skills, and dedication to professional growth, managers can gain a comprehensive understanding of each apprentice's potential, intentions, and overall suitability for the salon.</p>
		<div class="grid grid-cols-1 gap-6">
			<?php 
			$scales = [
				['name'=>'loyalty_score','label'=>'Loyalty'],
				['name'=>'long_term_commitment_score','label'=>'Long-term Commitment'],
				['name'=>'adaptability_score','label'=>'Adaptability'],
				['name'=>'interpersonal_skills_score','label'=>'Interpersonal Skills'],
				['name'=>'professional_growth_score','label'=>'Professional Growth']
			];
			foreach ($scales as $scale): $current = (int)($_POST[$scale['name']] ?? 0); ?>
			<div>
				<label class="block text-sm font-medium mb-2"><?= $scale['label'] ?> (1–5) <span style="color:#AF831A;">*</span></label>
				<div class="flex items-center gap-3">
					<?php for ($i=1;$i<=5;$i++): $checked = ($current===$i)?'checked':''; ?>
					<label class="inline-flex items-center gap-2 cursor-pointer">
						<input type="radio" name="<?= $scale['name'] ?>" value="<?= $i ?>" <?= $checked ?> class="checkbox-custom">
						<span class="text-sm"><?= $i ?></span>
					</label>
					<?php endfor; ?>
				</div>
				<?php if ($scale['name'] === 'loyalty_score'): ?>
				<div class="text-[11px] text-gray-600 mt-2 leading-snug">
					<div><strong>5 - exceptional loyalty:</strong> the apprentice consistently demonstrates dedication to our salon's values and culture, showing a strong commitment to long-term growth within our company.</div>
					<div><strong>4 - high loyalty:</strong> the apprentice regularly aligns with our salon's values and displays a commitment to our team and clients.</div>
					<div><strong>3 - moderate loyalty:</strong> the apprentice generally adheres to our values but may occasionally show signs of exploring other opportunities.</div>
					<div><strong>2 - low loyalty:</strong> the apprentice occasionally displays behaviors or attitudes inconsistent with our salon's values, hinting at possible intentions to leave in the future.</div>
					<div><strong>1 - minimal loyalty:</strong> the apprentice frequently exhibits behavior that contradicts our salon's values and may be on a path to departure.</div>
				</div>
				<?php endif; ?>
				<?php if ($scale['name'] === 'long_term_commitment_score'): ?>
				<div class="text-[11px] text-gray-600 mt-2 leading-snug">
					<div><strong>5 - strong long-term commitment:</strong> the apprentice consistently expresses a desire to build a long-lasting career within our salon, with a strong focus on professional growth.</div>
					<div><strong>4 - good long-term commitment:</strong> the apprentice actively shows interest in staying with us for the long term but may occasionally consider other options.</div>
					<div><strong>3 - neutral long-term commitment:</strong> the apprentice is uncertain about their long-term goals but remains engaged in their current role.</div>
					<div><strong>2 - limited long-term commitment:</strong> the apprentice occasionally expresses doubts about their future with us and explores alternatives.</div>
					<div><strong>1 - no long-term commitment:</strong> the apprentice often discusses plans to leave our salon in the near future.</div>
				</div>
				<?php endif; ?>
				<?php if ($scale['name'] === 'adaptability_score'): ?>
				<div class="text-[11px] text-gray-600 mt-2 leading-snug">
					<div><strong>5 - exceptional adaptability:</strong> the apprentice consistently handles challenges and changes with grace, displaying a high level of resilience.</div>
					<div><strong>4 - strong adaptability:</strong> the apprentice effectively adapts to various situations and is open to learning from both successes and failures.</div>
					<div><strong>3 - moderate adaptability:</strong> the apprentice generally adapts to challenges but may struggle with significant changes.</div>
					<div><strong>2 - limited adaptability:</strong> the apprentice occasionally faces difficulties when confronted with challenges or changes.</div>
					<div><strong>1 - poor adaptability:</strong> the apprentice often struggles to adapt and may become resistant to change.</div>
				</div>
				<?php endif; ?>
				<?php if ($scale['name'] === 'interpersonal_skills_score'): ?>
				<div class="text-[11px] text-gray-600 mt-2 leading-snug">
					<div><strong>5 - exceptional interpersonal skills:</strong> the apprentice consistently builds strong relationships with colleagues and clients, creating trust and loyalty.</div>
					<div><strong>4 - strong interpersonal skills:</strong> the apprentice effectively communicates and connects with others, fostering positive relationships.</div>
					<div><strong>3 - moderate interpersonal skills:</strong> the apprentice generally interacts well but may encounter occasional challenges in building rapport.</div>
					<div><strong>2 - limited interpersonal skills:</strong> the apprentice occasionally faces difficulties in communication and relationship-building.</div>
					<div><strong>1 - poor interpersonal skills:</strong> the apprentice often struggles to establish and maintain positive relationships.</div>
				</div>
				<?php endif; ?>
				<?php if ($scale['name'] === 'professional_growth_score'): ?>
				<div class="text-[11px] text-gray-600 mt-2 leading-snug">
					<div><strong>5 - exceptional dedication to growth:</strong> the apprentice displays a relentless commitment to learning, consistently seeking opportunities for improvement.</div>
					<div><strong>4 - strong dedication to growth:</strong> the apprentice actively pursues professional development but may occasionally need encouragement.</div>
					<div><strong>3 - moderate dedication to growth:</strong> the apprentice engages in learning opportunities but may not consistently pursue additional growth.</div>
					<div><strong>2 - limited dedication to growth:</strong> the apprentice occasionally participates in learning activities but shows limited enthusiasm.</div>
					<div><strong>1 - poor dedication to growth:</strong> the apprentice seldom takes initiative in their professional development.</div>
				</div>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="section-card">
		<h2 class="text-lg font-semibold mb-4">Remarks</h2>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<div>
				<label class="block text-sm font-medium mb-2">Remarks</label>
				<textarea name="remarks" rows="4" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Add remarks..."><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
			</div>
			<div>
				<label class="block text-sm font-medium mb-2">Additional Information</label>
				<textarea name="additional_info" rows="4" class="w-full border rounded-lg px-3 py-2 form-field resize-none" placeholder="Anything else you'd like to share?"><?= htmlspecialchars($_POST['additional_info'] ?? '') ?></textarea>
			</div>
		</div>
	</section>

	<section class="section-card">
		<button type="submit" class="w-full px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium">Submit Bi-Weekly Report</button>
	</section>

</form>

<script>
function biWeeklyForm() {
	return {
		dayCount: 0,
		startDate: '',
		endDate: '',
		init() {
			flatpickr("#dateRangePicker", {
				mode: "range",
				dateFormat: "Y-m-d",
				onChange: (selectedDates) => {
					if (selectedDates.length === 2) {
						this.startDate = selectedDates[0].toISOString().split('T')[0];
						this.endDate = selectedDates[1].toISOString().split('T')[0];
						this.calculateDays();
						const picker = document.getElementById('dateRangePicker');
						picker.value = this.startDate + ' to ' + this.endDate;
					} else if (selectedDates.length === 1) {
						this.startDate = selectedDates[0].toISOString().split('T')[0];
						this.endDate = '';
						this.dayCount = 0;
					} else {
						this.startDate = '';
						this.endDate = '';
						this.dayCount = 0;
					}
				}
			});
		},
		calculateDays() {
			if (this.startDate && this.endDate) {
				const start = new Date(this.startDate);
				const end = new Date(this.endDate);
				if (end >= start) {
					const diff = end.getTime() - start.getTime();
					this.dayCount = Math.ceil(diff / (1000 * 3600 * 24)) + 1;
				} else { this.dayCount = 0; }
			} else { this.dayCount = 0; }
		},
		formatDateRange() {
			if (this.startDate && this.endDate) {
				const s = new Date(this.startDate); const e = new Date(this.endDate);
				const fmt = d => d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
				return fmt(s) + ' to ' + fmt(e);
			}
			return '';
		}
	}
}
</script>

<?php require __DIR__.'/../includes/footer.php'; ?>


