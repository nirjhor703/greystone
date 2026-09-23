@if($storeUser)
    @php($storeWalletCoupons = $storeUser->walletCoupons()->with('coupon')->latest()->get())
    <button type="button" class="store-wallet-fab" data-wallet-open aria-label="Open coupon wallet"><span>🎟</span><b>Wallet</b><em>{{ $storeWalletCoupons->where('status', 'available')->count() }}</em></button>
    @include('members.partials.wallet-modal', ['walletCoupons' => $storeWalletCoupons])
    <script>
        document.addEventListener('DOMContentLoaded',()=>{const modal=document.getElementById('memberWalletModal');document.querySelectorAll('[data-wallet-open]').forEach(button=>button.addEventListener('click',()=>modal?.removeAttribute('hidden')));document.querySelectorAll('[data-wallet-close]').forEach(button=>button.addEventListener('click',()=>modal?.setAttribute('hidden','')))});
    </script>
@endif
