<div class="navbar-header" style="margin-bottom: 50px">
    <ul class="nav navbar-nav flex-row">
        <li class="nav-item mr-auto"><a class="navbar-brand" href="/dashboard">
                @if(!is_null(auth()->user()->organization))
                    <img src="{{ auth()->user()->organization->image_url}}" alt="avatar" height="40" width="80">
                @else
                    <img src="/images/logo/growthlab.png" alt="avatar" height="50" width="150">
                @endif
            </a>
        </li>
        <li class="nav-item nav-toggle">
            <a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i
                        class="bx bx-x d-block d-xl-none font-medium-4 primary"></i><i
                        class="toggle-icon bx bx-disc font-medium-4 d-none d-xl-block primary"
                        data-ticon="bx-disc"></i>
            </a>
        </li>

        <li class="nav-item mr-auto">
            <a class="navbar-brand" href="/dashboard">
                <h2 class="brand-text mb-0"
                    style="padding-right:0!important;">{{ is_null(auth()->user()->organization_id)?config('app.name') : auth()->user()->organization->name }}</h2>
            </a>
        </li>


    </ul>
</div>
<div class="shadow-bottom"></div>
<div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation"
        data-icon-style="lines">
        <li class="{{Request::is('dashboard','dashboard/final/*/*','dashboard/companyProfile/*','dashboard/cycles/view/*','dashboard/cycles/my/*','dashboard/screening/*/*')?'active':''}} nav-item">
            <a href="/dashboard"><i class="menu-livicon"
                                    data-icon="desktop"></i><span class="menu-title"
                >{{__('admin.Dashboard')}}</span></a>
        </li>
        <li class="{{Request::is('*myProfile')?'active':''}} nav-item">
            <a href="{{route('my_profile')}}">
                <i class="menu-livicon" data-icon="user"></i>
                <span class="menu-title">{{__('admin.Profile')}}</span>
            </a>
        </li>


        @if(auth()->user()->HaveCompany==1)
            <li class="{{Request::is('*myCompany')?'active':''}} nav-item">
                <a href="{{route('my_company')}}">
                    <i class="menu-livicon" data-icon="building"></i>
                    <span class="menu-title">{{__('admin.My company')}}</span>
                </a>
            </li>
        @endif
        <li class="{{Request::is('*calendar*')?'active':''}} nav-item">
            <a href="{{route('calendar')}}">
                <i class="menu-livicon" data-icon="calendar"></i>
                <span class="menu-title">{{__('admin.Calendar')}}</span>
            </a>
        </li>
        <?php
        $cmessages = App\Messenger::where('receiver', auth()->id())->whereNull('read');
//        if( !is_null( $organization_id ) ) { $cmessages = $cmessages->where('organization_id', $organization_id); }
        $unread = $cmessages->count();
        $l == 'ar' ? $arrow = 'left' : $arrow = 'right';
        ?>
        <li class="{{Request::is('dashboard/inbox','dashboard/outbox','dashboard/mymsg','dashboard/sendmessage')?'sidebar-group-active open':''}} nav-item ">
            <a href="#"><i class="menu-livicon" data-icon="comments"></i>
                <span class="menu-title">{{__('admin.My messages')}}</span>
            </a>
            <ul class="menu-content">
                <li class="{{Request::is('dashboard/sendmessage')?'active':''}}"><a href="/dashboard/sendmessage"><i
                                class="bx bx-message"></i>
                        <span class="menu-item">{{__('admin.New Message')}}</span>
                    </a>
                </li>
                <li class="{{Request::is('dashboard/inbox')?'active':''}}"><a href="/dashboard/inbox"><i
                                class="bx bxs-inbox"></i>
                        <span class="menu-item">{{__('admin.myinbox')}}</span>
                        @if($unread>0)
                            <span class="badge badge-pill badge-round badge-dark-info float-right mr-50 ml-auto">{{$unread}}</span>
                        @endif
                    </a>
                </li>
                <li class="{{Request::is('dashboard/outbox')?'active':''}}"><a href="/dashboard/outbox"><i
                                class="bx bx-mail-send"></i>
                        <span class="menu-item">{{__('admin.myoutbox')}}</span></a>
                </li>
                @can('view_messages')
                    <li class="{{Request::is('dashboard/mymsg')?'active':''}}"><a href="/dashboard/mymsg"><i
                                    class="bx bx-support"></i><span class="menu-item"
                            >{{__('admin.contactus')}}</span></a>
                    </li>
                @endcan
            </ul>
        </li>
        @if( !Auth::user()->hasanyRole("Mentor|Judger") )
            <li class="{{Request::is('dashboard/directory')?'active':''}} nav-item"><a href="/dashboard/directory">
                    <i class="menu-livicon" data-icon="search"></i>
                    <span class="menu-title">{{__('admin.Services')}}</span></a>
            </li>
        @endif
        @role('Registered')
        <li class="{{Request::is('dashboard/requests/mine','/dashboard/mentorship/mine')?'sidebar-group-active open':''}} nav-item ">
            <a href="#"><i class="menu-livicon" data-icon="two-pointers"></i>
                <span class="menu-title">{{__('admin.My Requests')}}</span>
            </a>
            <ul class="menu-content">

                <li class="{{Request::is('dashboard/requests/mine')?'active':''}}"><a href="/dashboard/requests/mine"><i
                                class="bx bx-{{$arrow}}-arrow-alt"></i>
                        <span class="menu-item">{{__('admin.Services')}}</span>
                    </a>
                </li>

                <li class="{{Request::is('dashboard/mentorship/mine')?'active':''}}"><a
                            href="/dashboard/mentorship/mine"><i class="bx bx-{{$arrow}}-arrow-alt"></i>
                        <span class="menu-item">{{__('admin.Mentorship')}}</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="{{Request::is('dashboard/training/my*')?'active':''}} nav-item"><a href="/dashboard/training/my">
                <i class="menu-livicon" data-icon="help"></i>
                <span class="menu-title">{{__('admin.My courses')}}</span></a>
        </li>
        @endrole
        <li class="{{Request::is('dashboard/myFiles')?'active':''}} nav-item"><a href="/dashboard/myFiles">
                <i class="menu-livicon" data-icon="save"></i>
                <span class="menu-title">{{__('admin.filemanager')}}</span></a>
        </li>
        @canany (['view_user','view_log','view_forms','view_report'])
            <li class=" navigation-header"><span>{{__('admin.Portal Settings')}}</span>
            </li>
        @endcanany
        @if( auth()->user()->hasRole('Admin') )
            <li class="{{Request::is('*organizations')?'active':''}} nav-item">
                <a href="{{route('organizations.index')}}">
                    <i class="menu-livicon" data-icon="building"></i>
                    <span class="menu-title">{{__('admin.Manage Organizations')}}</span>
                </a>
            </li>
        @endif
        @can ('edit_forms')
            <li class="{{Request::is('dashboard/show-builder*','dashboard/show-form*')?'active':''}} nav-item"><a
                        href="/dashboard/show-builder">
                    <i class="menu-livicon" data-icon="thumbnails-big"></i>
                    <span class="menu-title">{{__('admin.Forms builder')}}</span></a>
            </li>
        @endcan
        @can ('edit_cycles')
            {{--                    <li class="{{Request::is('dashboard/cycles*')?'active':''}} nav-item"><a href="/dashboard/cycles">--}}
            {{--                            <i class="menu-livicon" data-icon="retweet"></i>--}}
            {{--                            <span class="menu-title" >{{__('admin.Cycles')}}</span></a>--}}
            {{--                    </li>--}}
            <li class="{{Request::is('dashboard/cycles*')?'sidebar-group-active open':''}} nav-item">
                <a href="#"><i class="menu-livicon" data-icon="retweet"></i><span class="menu-title"
                    > {{__('admin.Cycles')}}</span></a>
                <ul class="menu-content">
                    <li class="{{Request::is('/dashboard/cycles*')?'active':''}}"><a href="/dashboard/cycles"><i
                                    class="bx bx-{{$arrow}}-arrow-alt"></i><span class="menu-item"
                            > {{__('cycles.steps')}}</span></a>
                    </li>
                    <li class="{{Request::is('dashboard/cycles/*/registered')?'active':''}}"><a
                                href="/dashboard/cycles/0/registered"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"
                            > {{__('admin.Startups')}}</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{Request::is('dashboard/screening*')?'sidebar-group-active open':''}} nav-item">
                <a href="#"><i class="menu-livicon" data-icon="hourglass"></i><span class="menu-title"
                    > {{__('admin.Screening')}}</span></a>
                <ul class="menu-content">
                    <li class="{{Request::is('dashboard/screening','dashboard/*/screening')?'active':''}}"><a
                                href="/dashboard/screening"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"
                            > {{__('admin.Manage')}}</span></a>
                    </li>
                    <li class="{{Request::is('dashboard/screening/results*')?'active':''}}"><a
                                href="/dashboard/screening/results"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"
                            > {{__('admin.Results')}}</span></a>
                    </li>
                </ul>
            </li>
            <li class="{{Request::is('dashboard/mentorship/assign*','dashboard/mentorship/view*','dashboard/mentorship/sessions*')?'sidebar-group-active open':''}} nav-item">
                <a href="#"><i class="menu-livicon" data-icon="idea"></i><span
                            class="menu-title"> {{__('admin.Mentorship')}}</span></a>
                <ul class="menu-content">
                    <li class="{{Request::is('dashboard/mentorship/assign*')?'active':''}}"><a
                                href="/dashboard/mentorship/assign"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"> {{__('admin.Cycles Mentors')}}</span></a>
                    </li>
                    <li class="{{Request::is('dashboard/mentorship/view*')?'active':''}}"><a
                                href="/dashboard/mentorship/view"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"> {{__('admin.Manage')}}</span></a>
                    </li>
                    {{--                            <li class="{{Request::is('dashboard/mentorship/sessions*')?'active':''}}"><a href="/dashboard/mentorship/sessions"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span class="menu-item" > {{__('admin.Sessions')}}</span></a>--}}
                    {{--                            </li>--}}

                    <li class="{{Request::is('dashboard/mentorship/sessions*')?'sidebar-group-active open':''}} nav-item">
                        <a href="#"><i class="bx bx-slider"></i><span class="menu-title"
                            > {{__('admin.Sessions')}}</span></a>
                        <ul class="menu-content">
                            <li class="{{Request::is('/dashboard/mentorship/sessions?show=all')?'active':''}}"><a
                                        href="/dashboard/mentorship/sessions?show=all"><i
                                            class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                            class="menu-item"> {{__('cycles.All requests')}}</span></a>
                            <li class="{{Request::is('/dashboard/mentorship/sessions')?'active':''}}"><a
                                        href="/dashboard/mentorship/sessions"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                            class="menu-item"> {{__('cycles.New requests only')}}</span></a>
                            <li class="{{Request::is('/dashboard/mentorship/sessions?show=approved')?'active':''}}"><a
                                        href="/dashboard/mentorship/sessions?show=approved"><i
                                            class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                            class="menu-item"> {{__('cycles.approved requests')}}</span></a>
                            <li class="{{Request::is('/dashboard/mentorship/sessions?show=disapproved')?'active':''}}">
                                <a href="/dashboard/mentorship/sessions?show=disapproved"><i
                                            class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                            class="menu-item"> {{__('cycles.disapproved requests')}}</span></a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="{{Request::is('*training*')?'sidebar-group-active open':''}} nav-item">
                <a href="#"><i class="menu-livicon" data-icon="info-alt"></i><span
                            class="menu-title"> {{__('admin.Training')}}</span></a>
                <ul class="menu-content">
                    <li class="{{Request::is('dashboard/training/sessions*')?'active':''}}"><a
                                href="/dashboard/training/sessions"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"> {{__('admin.Training sessions')}}</span></a>
                    </li>
                    <li class="{{Request::is('dashboard/training/all*','dashboard/training/addcontent*','dashboard/training/editcontent*','dashboard/training/viewcontent*')?'active':''}}">
                        <a href="/dashboard/training/all"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"> {{__('admin.Training courses')}}</span></a>
                    </li>
                    <li class="{{Request::is('dashboard/training/results*')?'active':''}}"><a
                                href="/dashboard/training/results"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"> {{__('admin.Results')}}</span></a>
                    </li>
                </ul>
            </li>
            <li class="{{Request::is('dashboard/final/*')?'sidebar-group-active open':''}} nav-item">
                <a href="#"><i class="menu-livicon" data-icon="trophy"></i><span class="menu-title"
                    > {{__('admin.Final Screening')}}</span></a>
                <ul class="menu-content">
                    <li class="{{Request::is('dashboard/final','dashboard/*/final')?'active':''}}"><a
                                href="/dashboard/final"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"
                            > {{__('admin.Manage')}}</span></a>
                    </li>
                    <li class="{{Request::is('dashboard/final/results*')?'active':''}}"><a
                                href="/dashboard/final/results"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                    class="menu-item"
                            > {{__('admin.Results')}}</span></a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-group-active open nav-item">
            </li>

            @if(!Auth::user()->hasanyRole("organizationManager|Mentor|Judger") )
                <li class="{{Request::is('*services*','','dashboard/requests/all','dashboard/room*')?'sidebar-group-active open':''}} nav-item">
                    <a href="#"><i class="menu-livicon" data-icon="loader-10"></i><span class="menu-title"
                        > {{__('admin.Services')}}</span></a>
                    <ul class="menu-content">
                        <li class="{{Request::is('dashboard/requests/all','dashboard/requests/old')?'active':''}}"><a
                                    href="{{route('requests')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i>
                                <span class="menu-item"> {{__('admin.Requests')}}</span></a>
                        </li>
                        <li class="{{Request::is('dashboard/services/create')?'active':''}}"><a
                                    href="{{route('services.create')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                        class="menu-item"
                                > {{__('admin.Add Service')}}</span></a>
                        </li>
                        <li class="{{Request::is('dashboard/services','dashboard/services/*/edit')?'active':''}}"><a
                                    href="{{route('services.index')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                        class="menu-item"
                                > {{__('admin.All Services')}}</span></a>
                        </li>
                        <!--
                            <li class="{{Request::is('dashboard/room*','')?'active':''}}"><a href="{{route('rooms.index')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span class="menu-item"
                                    > {{__('admin.Rooms Management')}}</span></a>
                            </li> -->
                        <li class="{{Request::is('dashboard/serviceCategories*')?'active':''}}"><a
                                    href="{{route('serviceCategories.index')}}"><i
                                        class="bx bx-{{$arrow}}-arrow-alt"></i><span class="menu-item"
                                > {{__('admin.Services Categories')}}</span></a>
                        </li>
                    </ul>
                </li>
            @endif
        @endcan
        @if(!Auth::user()->hasanyRole("organizationManager"))
            @can('view_user')
                <li class="{{Request::is('*users*')?'sidebar-group-active open':''}} nav-item">
                    <a href="#"><i class="menu-livicon" data-icon="users"></i><span class="menu-title"
                        > {{__('admin.Users')}}</span></a>
                    <ul class="menu-content">
                        <li class="{{Request::is('dashboard/users/create')?'active':''}}"><a
                                    href="{{route('users.create')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                        class="menu-item"
                                > {{__('admin.Add New')}}</span></a>
                        </li>
                        <li class="{{Request::is('dashboard/users','dashboard/users/*/edit')?'active':''}}"><a
                                    href="{{route('users.index')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                        class="menu-item"
                                > {{__('admin.List All')}}</span></a>
                        </li>
                        <li class="{{Request::is('dashboard/companies')?'active':''}}"><a
                                    href="{{route('users.companies')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span
                                        class="menu-item"
                                > {{__('admin.Companies')}}</span></a>
                        </li>
                        @can('view_permissions')
                            {{--                                <li class="{{Request::is('dashboard/permissions*')?'active':''}}"><a href="{{route('permissions.index')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span class="menu-item"--}}
                            {{--                                        > {{__('admin.Permissions')}}</span></a>--}}
                            {{--                                </li>--}}
                            {{--                                <li class="{{Request::is('dashboard/roles*')?'active':''}}"><a href="{{route('roles.index')}}"><i class="bx bx-{{$arrow}}-arrow-alt"></i><span class="menu-item"--}}
                            {{--                                        > {{__('admin.Roles')}}</span></a>--}}
                            {{--                                </li>--}}
                        @endcan
                    </ul>
                </li>
            @endcan
            @can('view_documentation')
                <li class="{{Request::is('dashboard/documentation*')?'active':''}} nav-item"><a
                            href="/dashboard/documentation">
                        <i class="menu-livicon" data-icon="morph-folder"></i>
                        <span class="menu-title">{{__('admin.Documentations')}}</span></a>
                </li>
            @endcan
            {{--                @can ('view_log')--}}
            {{--                    <li class="{{Request::is('*audit-logs*')?'active':''}} nav-item"><a href="/dashboard/audit-logs"><i class="menu-livicon"--}}
            {{--                                                                                                                    data-icon="lab"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.Log viewer')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
        @endif
            @can ('view_report')
                <li class="{{Request::is('reports*')?'active':''}} nav-item"><a href="{{ route('reports.index') }}"><i
                                class="menu-livicon" data-icon="notebook"></i>
                        <span class="menu-title">{{__('admin.Reports')}}</span></a>
                </li>
            @endcan
            <li class=" navigation-header"><span></span>
            </li>

            {{--                @can('edit_settings')--}}
            {{--                    <li class=" navigation-header"><span>{{__('admin.Frontend settings')}}</span>--}}
            {{--                    </li>--}}
            {{--                    <li class="{{Request::is('dashboard/settings')?'active':''}} nav-item"><a href="/dashboard/settings"><i class="menu-livicon"--}}
            {{--                                                                                                                    data-icon="wrench"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.Homesettings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                    <li class="{{Request::is('dashboard/setaboutus')?'active':''}} nav-item"><a href="/dashboard/setaboutus"><i--}}
            {{--                                    class="menu-livicon" data-icon="globe"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.About Settings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                    <li class="{{Request::is('dashboard/setcontactus')?'active':''}} nav-item"><a href="/dashboard/setcontactus"><i--}}
            {{--                                    class="menu-livicon" data-icon="paper-plane"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.Contact Settings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can ('manage_menus')--}}
            {{--                    <li class="{{Request::is('*menu*')?'active':''}} nav-item"><a href="/dashboard/menus"><i class="menu-livicon"--}}
            {{--                                                                                                         data-icon="morph-menu-arrow-bottom"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.Menusettings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can ('view_slide')--}}
            {{--                    <li class="{{Request::is('*slide*')?'active':''}} nav-item"><a href="/dashboard/slides"><i class="menu-livicon"--}}
            {{--                                                                                                           data-icon="settings"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.Slidersettings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can ('manage_pages')--}}
            {{--                    <li class="{{Request::is('*page*')?'active':''}} nav-item"><a href="/dashboard/pages"><i class="menu-livicon"--}}
            {{--                                                                                                         data-icon="briefcase"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.Pagesettings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can ('manage_posts')--}}
            {{--                    <li class="{{Request::is('*post*')?'active':''}} nav-item"><a href="/dashboard/posts"><i class="menu-livicon"--}}
            {{--                                                                                                         data-icon="box-add"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.Postsettings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can ('view_faqs')--}}
            {{--                    <li class="{{Request::is('*faqs*')?'active':''}} nav-item"><a href="/dashboard/faqs"><i class="menu-livicon"--}}
            {{--                                                                                                        data-icon="comments"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.Faqs settings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can ('view_partner')--}}
            {{--                    <li class="{{Request::is('*partners*')?'active':''}} nav-item"><a href="/dashboard/partners"><i class="menu-livicon"--}}
            {{--                                                                                                                data-icon="share"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.partners settings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can ('view_press')--}}
            {{--                    <li class="{{Request::is('*press*')?'active':''}} nav-item"><a href="/dashboard/press"><i class="menu-livicon"--}}
            {{--                                                                                                          data-icon="paper-clip"></i><span class="menu-title"--}}
            {{--                            >{{__('admin.press settings')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can('manage_events')--}}
            {{--                    <li class="{{Request::is('*event*')?'active':''}} nav-item"><a href="/dashboard/allevents"><i class="menu-livicon"--}}
            {{--                                                                                                              data-icon="calendar"></i><span class="menu-title">{{__('admin.manageevents')}}</span></a>--}}
            {{--                    </li>--}}
            {{--                @endcan--}}
            {{--                @can('manage_page')--}}
            {{--                    @if (Auth::user()->PageId)--}}
            {{--                        <li class="{{Request::is('editmypage')?'active':''}} nav-item"><a href="/dashboard/editmypage"><i class="menu-livicon"--}}
            {{--                                                                                                                      data-icon="briefcase"></i><span class="menu-title">{{__('admin.editmypage')}}</span></a>--}}
            {{--                        </li>--}}
            {{--                    @endif--}}
            {{--                @endcan--}}



    </ul>
</div>