<div class="member-wallet-modal member-glass-modal" id="memberWalletModal" hidden>
    <button type="button" class="member-wallet-backdrop member-glass-backdrop" data-wallet-close aria-label="Close coupon wallet"></button>
    <section role="dialog" aria-modal="true" aria-labelledby="memberWalletTitle">
        <header><div><small>MEMBER REWARDS</small><h2 id="memberWalletTitle">Coupon Wallet</h2></div><button type="button" data-wallet-close aria-label="Close coupon wallet">×</button></header>
        <div class="member-wallet-list">
            @forelse($walletCoupons as $walletCoupon)
                <article class="wallet-coupon {{ $walletCoupon->status !== 'available' ? 'is-used' : '' }}">
                    <div class="wallet-coupon-ticket">
                        <i><span>DISCOUNT</span><b>{{ $walletCoupon->coupon->discountLabel() }}</b></i>
                        <div><small>{{ str_starts_with($walletCoupon->source, 'milestone:') ? 'PURCHASE #'.\Illuminate\Support\Str::after($walletCoupon->source, ':').' REWARD' : 'MEMBER GIFT' }}</small><strong>COUPON</strong><code>{{ $walletCoupon->coupon->code }}</code><em>{{ $walletCoupon->coupon->expires_at ? 'VALID UNTIL '.$walletCoupon->coupon->expires_at->format('d M Y') : 'MEMBER EXCLUSIVE' }}</em></div>
                    </div>
                    <aside>@if($walletCoupon->status === 'available')<button type="button" data-wallet-coupon-apply="{{ $walletCoupon->coupon->code }}">APPLY</button>@else<span>✓ USED</span>@endif</aside>
                </article>
            @empty
                <div class="wallet-empty"><span>🎁</span><strong>No coupons yet</strong><p>Keep moving through your purchase milestones. Every coupon you unlock will appear here automatically.</p></div>
            @endforelse
        </div>
        <small class="wallet-note">One coupon can be used per transaction.</small>
    </section>
</div>
<script>
document.querySelectorAll('[data-wallet-coupon-apply]').forEach((button)=>{const sync=()=>{button.classList.toggle('is-applied',localStorage.getItem('pending_coupon_code')===button.dataset.walletCouponApply);button.textContent=button.classList.contains('is-applied')?'APPLIED':'APPLY'};sync();button.addEventListener('click',()=>{localStorage.setItem('pending_coupon_code',button.dataset.walletCouponApply);document.querySelectorAll('[data-wallet-coupon-apply]').forEach((item)=>{item.classList.remove('is-applied');item.textContent='APPLY'});sync()})});
</script>
