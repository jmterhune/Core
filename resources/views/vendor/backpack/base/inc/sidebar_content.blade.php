<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
@hasrole(['System Admin','JA'])
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
@endrole

@hasrole('System Admin')
    <!-- Users, Roles, Permissions -->
    <li class="nav-item nav-dropdown">
        <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> Authentication</a>
        <ul class="nav-dropdown-items">
            <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
            <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
            <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
        </ul>
    </li>

<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-cog"></i>Maintenance</a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> Logs</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('horizon') }}'><i class='nav-icon la la-terminal'></i> Horizon</a></li>
    </ul>
</li>
@endrole
@hasrole(['System Admin','Mediator'])
<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-balance-scale"></i>Mediation</a>

  <ul class="nav-dropdown-items">

    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/create') }}'>
        <i class='nav-icon la la-plus'></i>
        Civil Mediation Events
      </a>
    </li>
    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediationfamily/create') }}'>
        <i class='nav-icon la la-plus'></i>
        Family Mediation Events
      </a>
    </li>
    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/events') }}'>
        <i class='nav-icon la la-user-clock'></i>
        Mediation Events
      </a>
    </li>
    <!-- <li class='nav-item'>
      <a class='nav-link' href="{{ env('ATTORNEY_PORTAL_APP_URL') }}/mediation/sc-form" target="_blant">
        <i class='nav-icon la la-file-text'></i>
        Public Civil Mediation Form
      </a>
    </li> -->
    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/case/scformlist') }}'>
        <i class='nav-icon la la-check'></i>
        Online requests
      </a>
    </li>



    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/availableschedule') }}'>
        <i class='nav-icon la la-calendar'></i>
        Mediator Schedule
      </a>
    </li>

    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/notavailableschedule') }}'>
        <i class='nav-icon la la-calendar-times'></i>
        Not Available Schedule
      </a>
    </li>

    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/mediator') }}'>
        <i class='nav-icon la la-user'></i>
        Mediator
      </a>
    </li>

    <!-- <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/documents') }}'>
        <i class='nav-icon la la-file'></i>
        Documents
      </a>
    </li> -->

    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/report/week') }}'>
        <i class='nav-icon la la-calendar'></i>
        Weekly Report
      </a>
    </li>

    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/report/countystats') }}'>
        <i class='nav-icon la la-calendar'></i>
        County Stats
      </a>
    </li>

    <li class='nav-item'>
      <a class='nav-link' href='{{ backpack_url('mediation/report/mediator') }}'>
        <i class='nav-icon la la-calendar'></i>
        Mediator Stats
      </a>
    </li>

      <li class='nav-item'>
          <a class='nav-link' href='{{ backpack_url('mediation/instructions') }}'>
              <i class='nav-icon la la-calendar'></i>
              Email Instructions
          </a>
      </li>

  </ul>
</li>
@endrole
@if((backpack_user()->hasPermissionTo('modify attorneys') && backpack_user()->hasRole('Mediator')))
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('attorney') }}'><i class='nav-icon la la-user-circle'></i> Attorneys</a></li>
@endif
@hasrole(['System Admin'])
    <li class="nav-item nav-dropdown">
        <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon las la-user-cog"></i> JACS SA</a>
        <ul class="nav-dropdown-items">
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('attorney') }}'><i class='nav-icon la la-user-circle'></i> Attorneys</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('category') }}'><i class='nav-icon la la-stream'></i> Categories</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('county') }}'><i class='nav-icon la la-globe'></i> Counties</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('court') }}'><i class='nav-icon la la-landmark'></i> Courts</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('court-type') }}'><i class='nav-icon la la-tags'></i> Court types</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('court-permission') }}'><i class='nav-icon las la-calendar'></i> Court Permissions</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('docket') }}'><i class='nav-icon la la-print'></i> Docket Print</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('event') }}'><i class='nav-icon la la-user-clock'></i> Events</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('event-status') }}'><i class='nav-icon la la-route'></i> Event statuses</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('event-type') }}'><i class='nav-icon la la-tags'></i> Event types</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('holiday') }}'><i class='nav-icon las la-gifts'></i> Holidays</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('judge') }}'><i class='nav-icon las la-gavel'></i> Judges</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('motion') }}'><i class='nav-icon las la-thumbtack'></i> Motions</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('template') }}'><i class='nav-icon la la-object-ungroup'></i> Templates</a></li>
            <li class='nav-item'><a class='nav-link' href='{{ backpack_url('timeslot-crud') }}'><i class='nav-icon la la-clock'></i> Timeslots</a></li>
        </ul>
    </li>






@endrole


@hasrole('JA')
    @if(backpack_user()->hasPermissionTo('modify attorneys','web'))
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('attorney') }}'><i class='nav-icon la la-user-circle'></i> Attorneys</a></li>
    @endcan
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('court') }}'><i class='nav-icon la la-landmark'></i> Courts</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('court-permission') }}'><i class='nav-icon las la-calendar'></i> Active Calendar</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('docket') }}'><i class='nav-icon la la-print'></i> Docket Print</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('event') }}'><i class='nav-icon la la-user-clock'></i> Events</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('template') }}'><i class='nav-icon la la-object-ungroup'></i> Templates</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('timeslot-crud') }}'><i class='nav-icon la la-clock'></i> Timeslots</a></li>


@endrole
@hasrole(['System Admin','JA'])
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('quick-reference') }}"><i class="la la-file-text-o"></i> Quick Reference</a></li>
@endrole

{{--<li class='nav-item'><a class='nav-link' href='{{ backpack_url('tickets') }}'><i class='nav-icon la la-question-circle'></i> Support</a></li>--}}


{{--<li class="nav-item"><a class="nav-link" href="{{ backpack_url('court-template-order') }}"><i class="nav-icon la la-question"></i> Court template orders</a></li>--}}
