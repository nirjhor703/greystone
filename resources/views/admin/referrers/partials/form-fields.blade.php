<div class="brand-form-sections referrer-form-sections">
    <section class="brand-form-section referrer-form-section">
        <div class="brand-form-section-title">
            <h4>Referrer Information</h4>
        </div>

        <div class="brand-form-grid">
            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_name">Referrer Name <span>*</span></label>
                <input id="{{ $formPrefix }}_name" type="text" name="name" placeholder="Example: Nirjhor Ahmed" autocomplete="off" data-referrer-name-input="{{ $formPrefix }}">
                <small class="brand-field-error name_error"></small>
            </div>

            <div class="brand-form-field referrer-code-field">
                <label for="{{ $formPrefix }}_code">Referral Code <span>*</span> @include('admin.partials.info-help', ['key' => 'referrer_form_code', 'text' => 'Customer checkout/order-e use korar unique referral code. Editable.'])</label>
                <div class="referrer-code-input-wrap">
                    <input id="{{ $formPrefix }}_code" type="text" name="code" placeholder="Auto generated code" style="text-transform:uppercase" autocomplete="off" data-referrer-code-input="{{ $formPrefix }}">
                    <button type="button" class="referrer-regenerate-code" data-regenerate-code="{{ $formPrefix }}">Generate</button>
                </div>
                <small class="referrer-code-status" data-code-status="{{ $formPrefix }}"></small>
                <small class="brand-field-error code_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_commission_rate">Commission Rate (%) <span>*</span> @include('admin.partials.info-help', ['key' => 'referrer_form_commission', 'text' => 'Product total after coupon discount-er upor commission percentage.'])</label>
                <input id="{{ $formPrefix }}_commission_rate" type="number" name="commission_rate" min="0" max="100" step="0.01" value="1">
                <span class="brand-field-help">Calculated on product total after coupon discount. Delivery charge is excluded.</span>
                <small class="brand-field-error commission_rate_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_gift_balance">Gift Balance (৳) @include('admin.partials.info-help', ['key' => 'referrer_form_gift', 'text' => 'Commission-er baire manual bonus/help balance.'])</label>
                <input id="{{ $formPrefix }}_gift_balance" type="number" name="gift_balance" min="0" step="0.01" value="0">
                <span class="brand-field-help">Manual bonus/help amount outside commission.</span>
                <small class="brand-field-error gift_balance_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_member_id">Linked Member</label>
                <select id="{{ $formPrefix }}_member_id" name="member_id">
                    <option value="">Not linked</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }} — {{ $member->email }}</option>
                    @endforeach
                </select>
                <small class="brand-field-error member_id_error"></small>
            </div>

            <div class="brand-form-field">
                <label for="{{ $formPrefix }}_is_active">Status</label>
                <select id="{{ $formPrefix }}_is_active" name="is_active">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <small class="brand-field-error is_active_error"></small>
            </div>
        </div>
    </section>
</div>
