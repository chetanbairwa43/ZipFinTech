<nav class="sidebar sidebar-offcanvas" id="sidebar">
	<ul class="nav">
	  @if((Auth::user()->roles->contains('5') && in_array('dashboard',$staffPermissions)) || Auth::user()->roles->contains('1'))
		<li class="nav-item">
			<a class="nav-link" href="{{ route('admin.dashboard') }}">
				<i class="mdi mdi-grid-large menu-icon"></i>
				<span class="menu-title">Dashboard</span>
			</a>
		</li>
		@endif

		@can('user_management_access')
		@if((Auth::user()->roles->contains('5') && in_array('user_management',$staffPermissions)) || Auth::user()->roles->contains('1'))
		<li class="nav-item {{ ((request()->is('admin/users*')) || (request()->is('admin/permiffssions*')) || (request()->is('admin/rolffes*'))) ? 'active' : '' }}">
			<a class="nav-link" data-bs-toggle="collapse" href="#user" aria-expanded="{{ ((request()->is('admin/users*')) || (request()->is('admin/permisffsions*')) || (request()->is('admin/roffles*'))) ? 'true' : 'false' }}" aria-controls="user">
				<i class="menu-icon mdi mdi-account-circle-outline"></i>
				<span class="menu-title">Users Management</span>
				<i class="menu-arrow"></i>
			</a>
			<div class="collapse {{ ((request()->is('admin/users*')) || (request()->is('admin/permisffsions*')) || (request()->is('admin/rolffes*'))) ? 'show' : '' }}" id="user">
				<ul class="nav flex-column sub-menu">
				@can('user_access')
				@if((Auth::user()->roles->contains('5') && in_array('users',$staffPermissions)) || Auth::user()->roles->contains('1'))
				<li class="nav-item"> <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"> Users </a></li>
				<li class="nav-item"> <a class="nav-link {{ request()->is('admin/fincra-user*') ? 'active' : '' }}" href="{{ route('admin.fincra-user') }}"> Audit users </a></li>
				<li class="nav-item"> <a class="nav-link {{ request()->is('admin/fincra-beneficiaries*') ? 'active' : '' }}" href="{{ route('admin.fincra-beneficiaries') }}"> Beneficiaries Users </a></li>
				@endif
				@endcan
				</ul>
			</div>
		</li>
		@endif
		@endcan

		@can('page_access')
		@if((Auth::user()->roles->contains('5') && in_array('pages',$staffPermissions)) || Auth::user()->roles->contains('1'))
		<li class="nav-item {{ request()->is('admin/pages*') ? 'active' : '' }}">
			<a class="nav-link" href="{{ route('admin.pages.index') }}">
				<i class="mdi mdi-book-open-page-variant menu-icon"></i>
				<span class="menu-title">Pages</span>
			</a>
		</li>
		@endif
		@endcan

		@can('bankloan_access')
		{{-- @if((Auth::user()->roles->contains('5') && in_array('bankloan',$staffPermissions)) || Auth::user()->roles->contains('1')) --}}
		<li class="nav-item {{ request()->is('admin/bankloan*') ? 'active' : '' }}">
			<a class="nav-link" href="{{ route('admin.bankloan.index') }}">
				<i class="mdi mdi-comment-question-outline menu-icon"></i>
				<span class="menu-title">Loan Details</span>
			</a>
		</li>
		{{-- @endif --}}
		{{-- @can('category_access')

		<li class="nav-item {{ request()->is('admin/support-categories*') ? 'active' : '' }}">
			<a class="nav-link" href="{{ route('admin.support-categories.index') }}">
				<i class="mdi mdi-format-list-bulleted menu-icon"></i>
				<span class="menu-title">Support Category</span>
			</a>
		</li>

		@endcan --}}
		@endcan
        {{-- <li class="nav-item {{ request()->is('admin/support-queries*') ? 'active' : '' }}">
			<a class="nav-link" href="{{ route('admin.support-queries.index') }}">
				<i class="mdi mdi-face-agent menu-icon"></i>
				<span class="menu-title">Support Queries</span>
			</a>
		</li> --}}
		{{-- @endif support-queries--}}


	 {{-- @can('wallet_transaction_access')

		<li class="nav-item {{ request()->is('admin/withdrawal-requests*') ? 'active' : '' }}">
			<a class="nav-link" href="{{ route('admin.withdrawal-requests.index') }}">
				<i class="mdi mdi-wallet menu-icon"></i>
				<span class="menu-title" >Withdrawal Requests</span>
			</a>
		</li>

		@endcan --}}
		<li class="nav-item {{  ((request()->is('admin/transactions*')) ||  (request()->is('admin/request-money*')) || (request()->is('admin/sending-money*')) || (request()->is('admin/single-user-transaction*')) || (request()->is('admin/customer-balance*'))) ? 'active' : '' }}">
			<a class="nav-link" data-bs-toggle="collapse" href="#transactions" aria-expanded="{{ ((request()->is('admin/transactions*')) || (request()->is('admin/single-user-transaction*')) ||  (request()->is('admin/request-money*')) || (request()->is('admin/sending-money*')) || (request()->is('admin/customer-balance*'))) ? 'true' : '' }}" aria-controls="transactions">
				<i class="mdi mdi-settings menu-icon"></i>
				<span class="menu-title">Transactions</span>
				<i class="menu-arrow"></i>
			</a>
			<div class="collapse {{ ((request()->is('admin/transactions*')) || (request()->is('admin/sending-money*')) || (request()->is('admin/request-money*')) || (request()->is('admin/single-user-transaction*')) || (request()->is('admin/withdrawal-requests*')) || (request()->is('admin/customer-balance*'))) ? 'show' : '' }}" id="transactions">
				<ul class="nav flex-column sub-menu">
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/transactions*') ? 'active' : '' }}" href="{{ route('admin.transactions.index') }}">All Transactions </a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/customer-balance*') ? 'active' : '' }}" href="{{ route('admin.customer-balance') }}"> Customer Balance </a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/transaction-utilities*') ? 'active' : '' }}" href="{{ route('admin.transaction-utilities') }}"> Utilities  </a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/sending-money*') ? 'active' : '' }}" href="{{ route('admin.sending-money') }}"> Sending Money  </a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/request-money*') ? 'active' : '' }}" href="{{ route('admin.request-money') }}"> Request Money  </a>
					</li>
                    <li class="nav-item">
						<a class="nav-link {{ request()->is('admin/withdrawal-requests*') ? 'active' : '' }}" href="{{ route('admin.withdrawal-requests.index') }}">Withdrawal Requests </a>
					</li>
				</ul>
			</div>

		</li>

		{{-- Roles and permisson --}}
		<li class="nav-item {{  ((request()->is('admin/permissions*')) || (request()->is('admin/roles*'))) ? 'active' : '' }}">
			<a class="nav-link" data-bs-toggle="collapse" href="#permissions" aria-expanded="{{ ((request()->is('admin/permissions*')) || (request()->is('admin/roles*'))) ? 'true' : '' }}" aria-controls="permissions">
				<i class="mdi mdi-security menu-icon"></i>
				<span class="menu-title">Roles and Permission</span>
				<i class="menu-arrow"></i>
			</a>
			<div class="collapse {{ ((request()->is('admin/permissions*')) || (request()->is('admin/roles*'))) ? 'show' : '' }}" id="permissions">
				<ul class="nav flex-column sub-menu">
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}"> Roles </a>
					</li>
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/permissions*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}"> Permission </a>
					</li>
				</ul>
			</div>

		</li>


		@can('email_template_access')
		@if((Auth::user()->roles->contains('5') && in_array('email_templates',$staffPermissions)) || Auth::user()->roles->contains('1'))
		<li class="nav-item {{ request()->is('admin/email-templates*') ? 'active' : '' }}">
			<a class="nav-link" href="{{ route('admin.email-templates.index') }}">
				<i class="mdi mdi-email-outline menu-icon"></i>
				<span class="menu-title">Email Templates</span>
			</a>
		</li>
		@endif
		@endcan

		@can('faq_access')
		@if((Auth::user()->roles->contains('5') && in_array('faq',$staffPermissions)) || Auth::user()->roles->contains('1'))
		<li class="nav-item {{ request()->is('admin/faqs*') ? 'active' : '' }}">
			<a class="nav-link" href="{{ route('admin.faqs.index') }}">
				<i class="mdi mdi-comment-question-outline menu-icon"></i>
				<span class="menu-title">FAQ's</span>
			</a>
		</li>
		@endif
		@endcan

		{{-- @if((Auth::user()->roles->contains('5') && in_array('faq',$staffPermissions)) || Auth::user()->roles->contains('1'))
			<li class="nav-item {{ request()->is('admin/faqs*') ? 'active' : '' }}">
				<a class="nav-link" href="{{ route('admin.faqs.index') }}">
					<i class="mdi mdi-comment-question-outline menu-icon"></i>
					<span class="menu-title">Fees</span>
				</a>
			</li>
		@endif --}}

		@can('user_management_access')
		@if((Auth::user()->roles->contains('5') && in_array('user_management',$staffPermissions)) || Auth::user()->roles->contains('1'))
		<li class="nav-item {{ ((request()->is('admin/virtualCard*')) || (request()->is('admin/permiffssions*')) || (request()->is('admin/rolffes*'))) ? 'active' : '' }}">
			<a class="nav-link" data-bs-toggle="collapse" href="#virtualCard" aria-expanded="{{ ((request()->is('admin/virtualCard*')) || (request()->is('admin/permisffsions*')) || (request()->is('admin/roffles*'))) ? 'true' : 'false' }}" aria-controls="virtualCard">
				<i class="mdi mdi-card-bulleted menu-icon"></i>
				<span class="menu-title">Virtual Card Management</span>
				<i class="menu-arrow"></i>
			</a>
			<div class="collapse {{ ((request()->is('admin/virtualCard*')) || (request()->is('admin/permisffsions*')) || (request()->is('admin/rolffes*'))) ? 'show' : '' }}" id="virtualCard">
				<ul class="nav flex-column sub-menu">
				@can('user_access')
				@if((Auth::user()->roles->contains('5') && in_array('users',$staffPermissions)) || Auth::user()->roles->contains('1'))
				<li class="nav-item"> <a class="nav-link {{ request()->is('admin/virtualCard*') ? 'active' : '' }}" href="{{ route('admin.virtual-card.index') }}"> Virtual Cards </a></li>
				<li class="nav-item"> <a class="nav-link {{ request()->is('admin/virtual-card-holder*') ? 'active' : '' }}" href="{{ route('admin.virtual-card-holder.index') }}"> Virtual Card Holders </a></li>
				@endif
				@endcan
				</ul>
			</div>
		</li>
		@endif
		@endcan


		{{-- <li class="nav-item {{ request()->is('admin/virtualCard*') ? 'active' : '' }}">
			<a class="nav-link" href="{{ route('admin.virtual-card.index') }}">
				<i class="mdi mdi-card-bulleted menu-icon"></i>
				<span class="menu-title">Virtual cards</span>
			</a>

			<ul class="submenu">
				<li class="nav-item {{ request()->is('admin/virtual-card-holder*') ? 'active' : '' }}">
					<a class="nav-link" href="{{ route('admin.virtual-card-holder.index') }}">
						<i class="mdi mdi-account menu-icon"></i>
						<span class="menu-title">Card Holder</span>
					</a>
				</li>
			</ul>
		</li> --}}


		@can('setting_access')
		@if((Auth::user()->roles->contains('5') && in_array('setting_management',$staffPermissions)) || Auth::user()->roles->contains('1'))
		<li class="nav-item {{  (request()->is('admin/app-setting*')) ? 'active' : '' }}">
			<a class="nav-link" data-bs-toggle="collapse" href="#setting" aria-expanded="{{ (request()->is('admin/app-setting*')) ? 'true' : 'false' }}" aria-controls="setting">
				<i class="mdi mdi-settings menu-icon"></i>
				<span class="menu-title">Settings</span>
				<i class="menu-arrow"></i>
			</a>
			<div class="collapse {{ (request()->is('admin/app-setting*')) ? 'show' : '' }}" id="setting">
				<ul class="nav flex-column sub-menu">
			    @if((Auth::user()->roles->contains('5') && in_array('app_setting',$staffPermissions)) || Auth::user()->roles->contains('1'))
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/app-setting*') ? 'active' : '' }}" href="{{ route('admin.app-setting.index') }}"> App Setting </a>
					</li>
                @endif
				@if((Auth::user()->roles->contains('5') && in_array('fees',$staffPermissions)) || Auth::user()->roles->contains('1'))
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/fees*') ? 'active' : '' }}" href="{{ route('admin.fees.index') }}"> Fees </a>
					</li>
				@endif
				</ul>
			</div>
			{{-- <div class="collapse {{ (request()->is('admin/fees*')) ? 'show' : '' }}" id="setting">
				<ul class="nav flex-column sub-menu">
			    @if((Auth::user()->roles->contains('5') && in_array('fees',$staffPermissions)) || Auth::user()->roles->contains('1'))
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/fees*') ? 'active' : '' }}" href="{{ route('admin.fees.index') }}"> Fees </a>
					</li>
                @endif
				</ul>
			</div> --}}
<!--
			<div class="collapse {{ (request()->is('admin/domain-setting*')) ? 'show' : '' }}" id="setting">
				<ul class="nav flex-column sub-menu">
			   @if((Auth::user()->roles->contains('5') && in_array('domain_setting',$staffPermissions)) || Auth::user()->roles->contains('1'))
					<li class="nav-item">
						<a class="nav-link {{ request()->is('admin/domain-setting*') ? 'active' : '' }}" href="{{ route('admin.domain-setting.index') }}"> Domain Setting </a>
					</li>
                @endif
				</ul>
			</div> -->
		</li>
		@endif
		@endcan
	</ul>
</nav>
