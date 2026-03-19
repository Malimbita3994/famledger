<!-- Bottom Navigation Bar -->
<div class="bottom-nav hidden lg:flex">
  <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="ki-filled ki-home-3"></i><br/>Home
  </a>
  <a href="{{ route('families.reports.index') }}" class="{{ request()->routeIs('families.reports.*') ? 'active' : '' }}">
    <i class="ki-filled ki-chart-pie-simple"></i><br/>Reports
  </a>
  <a href="{{ route('families.wallets.index') }}" class="{{ request()->routeIs('families.wallets.*') ? 'active' : '' }}">
    <i class="ki-filled ki-wallet"></i><br/>Wallet
  </a>
  <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
    <i class="ki-filled ki-profile-circle"></i><br/>Me
  </a>
</div>
