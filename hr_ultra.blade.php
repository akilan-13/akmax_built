@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
  'resources/assets/vendor/libs/swiper/swiper.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('page-style')
<!-- Page -->
@vite([
  'resources/assets/vendor/scss/pages/cards-statistics.scss',
  'resources/assets/vendor/scss/pages/cards-analytics.scss'
])
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/apex-charts/apexcharts.js',
  'resources/assets/vendor/libs/swiper/swiper.js'
])
@endsection

@section('page-script')
@vite('resources/assets/js/dashboards-crm.js')
@endsection
@section('content')
 @php
  
    $helper = new \App\Helpers\Helpers();
    $common_date_format = $helper->general_setting_data()->date_format ?? 'd-M-y';
    $user_id = auth()->user()->user_id ;
    $auth_id = auth()->user()->id ;
    $userData =$helper->get_staff_data($user_id);
    $lastLogin =$helper->get_user_last_login($auth_id);
    $welcome_status = $userData->welcome_status ?? '1';
  @endphp
  <style>
    .ultra-filter {
        overflow: visible !important;
    }

    .select2-container {
        z-index: 99999 !important;
    }

    .select2-dropdown {
        z-index: 99999 !important;
    }
  </style>

    <div class="container-fluid py-4" >

    <div class="loading-custom" id="loadingScreenCustomize"  style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.9); z-index: 9999; display:none;">
        <div class="loading-content" style="text-align: center; position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%);">
            <img src="{{ asset('assets/common/logo_small.png') }}" alt="Company Logo" style="max-width: 120px; margin-bottom: 20px;height:100%">
            <div class="d-block">
            <div class="dot-custom red"></div>
            <div class="dot-custom orange"></div>
            <div class="dot-custom yellow"></div>
            <div class="dot-custom green"></div>
            <div class="dot-custom teal"></div>
            <div class="dot-custom blue"></div>
            <div class="dot-custom violet"></div>
            </div>
        </div>
    </div>

        <!-- TOP GREETING -->
        <div class="grid">
            <!-- User Profile Card -->
            <div class="card profile-card animate-fade-in position-relative overflow-hidden"
                style="background: linear-gradient(135deg, rgb(171 43 34),rgb(171 43 34), rgb(251 169 25)); color: white;">

                <!-- BACKGROUND IMAGE (Right Side Decoration) -->
                <img src="/assets/common/egc-CRM-login-png.png"
                    class="profile-bg-img"
                    alt="Decorative BG">

                <div class="d-flex position-relative justify-content-start w-100">
                    <!-- LEFT SIDE — 70% -->
                    <div class="flex-grow-1" >
                        <div class="profile-image-container mb-2">
                            <div class="profile-image-wrapper">
                                <div class="profile-border"></div>

                                @if(Auth::user()->staff->company_type==1)
                                <img  src="{{ Auth::user() && Auth::user()->staff && Auth::user()->staff->staff_image && file_exists(public_path('staff_images/Management/' . Auth::user()->staff->staff_image))
                                                    ? asset('staff_images/Management/' . Auth::user()->staff->staff_image)
                                                    : asset('assets/egc_images/auth/user_1.png') }}" alt="Profile" class="rounded-circle profile-img" />
                                @else
                                <img  src="{{ Auth::user() && Auth::user()->staff && Auth::user()->staff->staff_image && file_exists(public_path('staff_images/Buisness/'.Auth::user()->staff->company_id.'/'.Auth::user()->staff->entity_id.'/' . Auth::user()->staff->staff_image))
                                                    ? asset('staff_images/Buisness/'.Auth::user()->staff->company_id.'/'.Auth::user()->staff->entity_id.'/' . Auth::user()->staff->staff_image)
                                                    : asset('assets/egc_images/auth/user_1.png') }}" alt="Profile" class="rounded-circle profile-img" />
                                @endif
                            </div>
                        </div>

                        <div class="profile-info">
                            <h2 class="text-white">
                                Welcome,
                                @if(Auth::check())
                                    {{ Auth::user()->staff ? Auth::user()->staff->staff_name : 'Super Admin' }}
                                @else
                                    John Doe
                                @endif
                                !
                            </h2>
                            @php
                                $loginAt = \Carbon\Carbon::parse($lastLogin->login_at);

                                if($loginAt->isToday()) {
                                    $lastlogindate = 'Today, ' . $loginAt->format('h:i A');
                                } elseif($loginAt->isYesterday()) {
                                    $lastlogindate = 'Yesterday, ' . $loginAt->format('h:i A');
                                } else {
                                    $lastlogindate = $loginAt ? $loginAt->format('d-M-Y') : '-';
                                }
                            @endphp

                            <p>{{$userData->job_role_name ?? ''}} | Last login: {{$lastlogindate}}</p>

                            <!-- <div class="profile-stats d-flex">
                                <div class="stat me-4">
                                    <div class="stat-value">24</div>
                                    <div class="stat-label">Projects</div>
                                </div>
                                <div class="stat me-4">
                                    <div class="stat-value">89%</div>
                                    <div class="stat-label">Efficiency</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-value">126</div>
                                    <div class="stat-label">Tasks Done</div>
                                </div>
                            </div> -->
                        </div>
                    </div>

                    <!-- RIGHT SIDE CONTENT (optional) -->
                </div>
            </div>



            <!-- Date + Quote Card -->
            <div class="card date-card animate-fade-in delay-1" style="background: linear-gradient(135deg, rgb(171 43 34),rgb(171 43 34), rgb(251 169 25)); color: white;">
                <div class="date-info">
                    <h3 class="text-white"><i class="far fa-calendar-alt text-white"></i> Today</h3>
                    <div class="current-date" id="today-date">19 Nov 2025</div>
                </div>
                <div class="quote-container">
                    <div class="quote" id="positive-quote">"Stay motivated and positive!"</div>
                    <div class="author" id="quote-author"></div>
                </div>
            </div>
        </div>

        <div class="card filter-bar mb-4">
            <div class="row align-items-center">

                <div class="col-md-3">
                    <select class="form-select" id="companyFilter">
                        <option value="">All Companies</option>
                        @foreach($companies ?? [] as $c)
                            <option value="{{ $c->sno }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select class="form-select" id="entityFilter"></select>
                </div>

                <div class="col-md-3">
                    <select class="form-select" id="departmentFilter"></select>
                </div>

                <div class="col-md-3 text-end">
                    <span class="live-dot"></span> Live Data
                </div>

            </div>
        </div>

        <div class="kpi-row mb-4">

            <div class="kpi-box">
                <span>Total Staff</span>
                <h2 id="totalStaff">0</h2>
            </div>

            <div class="kpi-box success">
                <span>Present</span>
                <h2 id="present">0</h2>
            </div>

            <div class="kpi-box warning">
                <span>Late</span>
                <h2 id="late">0</h2>
            </div>

            <div class="kpi-box danger">
                <span>Absent</span>
                <h2 id="absent">0</h2>
            </div>

            <div class="kpi-box info">
                <span>Permission</span>
                <h2 id="permission">0</h2>
            </div>

        </div>

        <div class="entity-row mb-4" id="entityCounts"></div>

        <div class="card ultra-card mb-4">
            <h5>🚨 Today's Workforce Status</h5>

            <div class="row">

                <div class="col-md-4">
                    <h6 class="text-danger">Late Employees</h6>
                    <div class="mini-list" id="lateList"></div>
                </div>

                <div class="col-md-4">
                    <h6 class="text-dark">Absent Employees</h6>
                    <div class="mini-list" id="absentList"></div>
                </div>

                <div class="col-md-4">
                    <h6 class="text-warning">Permission</h6>
                    <div class="mini-list" id="permissionList"></div>
                </div>

            </div>
        </div>

        <div class="row mb-4">

            <div class="col-md-5">
                <div class="card ultra-card h-100">
                    <h5>🚨 Alerts</h5>
                    <div id="alertsContainer"></div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card ultra-card h-100">
                    <h5>📡 Live Activity</h5>
                    <div id="activityContainer"></div>
                </div>
            </div>

        </div>

        <div class="row mb-4">

            <div class="col-lg-8">
                <div class="card ultra-card p-4">
                    <h5>Attendance Analytics</h5>
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            

        </div>
        <div class="card ultra-card p-4 mb-4">
            <h5>🧠 AI Insights</h5>
            <div id="aiInsights"></div>
        </div>
    </div>
<style>
 .kpi-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
}

.kpi-box {
    flex: 1;
    background: #fff;
    padding: 15px;
    border-radius: 10px;
    text-align: left;
    border: 1px solid #eee;
}

.kpi-box h2 {
    margin: 5px 0 0;
    font-weight: 700;
}

.kpi-box span {
    font-size: 13px;
    color: #777;
}

.kpi-box.success { border-left: 5px solid #28a745; }
.kpi-box.warning { border-left: 5px solid #ffc107; }
.kpi-box.danger { border-left: 5px solid #dc3545; }
.kpi-box.info { border-left: 5px solid #007bff; }

.mini-list {
    max-height: 250px;
    overflow-y: auto;
}

.mini-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-bottom: 1px solid #eee;
}

.avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #ab2b22;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.alert-box {
    background: #fff3cd;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
}

.empty {
    text-align: center;
    color: #999;
    padding: 10px;
}
</style>
<style>
    .grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 16px;
    }
      
      /* Card Styles */
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #eee;
            box-shadow: none;
        }

     


      /* Profile Card */
      .profile-card {
          grid-column: span 2;
          display: flex;
          align-items: center;
          background: linear-gradient(135deg, var(--primary), var(--primary-dark));
          padding: 30px;
          position: relative;
          overflow: hidden;
      }

      

      .profile-image-container {
          position: relative;
          margin-right: 24px;
          z-index: 2;
      }

      .profile-img {
          width: 120px;
          height: 120px;
          border-radius: 50%;
          object-fit: cover;
          border: 4px solid rgba(255, 255, 255, 0.3);
          box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.1);
          position: relative;
          z-index: 2;
      }
      .profile-image-wrapper {
          position: relative;
          width: 120px;
          height: 120px;
          flex-shrink: 0; /* prevent distortion */
      }
      
      .profile-image-container {
        margin-left: -18px; /* adjust */
      }
.entity-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
}

.entity-box {
    background: #fff;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #eee;
    transition: 0.2s;
}

.entity-box:hover {
    transform: translateY(-3px);
}

.entity-top {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #777;
}

.entity-name {
    font-weight: 600;
    color: #333;
}

.entity-count {
    font-size: 22px;
    font-weight: 700;
    margin-top: 8px;
}


      .profile-info {
          z-index: 2;
          position: relative;
      }

      .profile-info h2 {
          font-size: 28px;
          font-weight: 700;
          margin-bottom: 8px;
      }

      .profile-info p {
          font-size: 16px;
          opacity: 0.9;
          margin-bottom: 16px;
      }

      .profile-stats {
          display: flex;
          gap: 20px;
      }

      .stat {
          text-align: center;
      }

      .stat-value {
          font-size: 20px;
          font-weight: 700;
          padding: 6px 14px;
          border: 2px solid rgba(255, 255, 255, 0.4);
          border-radius: 12px;
          display: inline-block;
          backdrop-filter: blur(4px);
      }


      .stat-label {
          font-size: 14px;
          opacity: 0.8;
      }

      /* Date Card */
      .date-card {
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          background: linear-gradient(135deg, var(--secondary), #059669);
      }

      .date-info h3 {
          font-size: 18px;
          margin-bottom: 8px;
          display: flex;
          align-items: center;
          gap: 8px;
      }

      .date-info h3 i {
          font-size: 20px;
      }

      .current-date {
          font-size: 32px;
          font-weight: 700;
          margin: 16px 0;
      }

      .quote-container {
          margin-top: 16px;
      }

      .quote {
          font-style: italic;
          margin-bottom: 12px;
          line-height: 1.5;
      }

      .weather {
          display: flex;
          align-items: center;
          gap: 8px;
          font-size: 14px;
      }

      /* Stats Cards */
      .stats-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 20px;
          margin-bottom: 24px;
      }

      .stat-card {
          text-align: center;
          padding: 20px;
      }

      .stat-icon {
          font-size: 32px;
          margin-bottom: 12px;
          height: 70px;
          width: 70px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 50%;
          margin: 0 auto 16px;
          background: rgba(255, 255, 255, 0.1);
      }

      .stat-card .value {
          font-size: 32px;
          font-weight: 700;
          margin-bottom: 8px;
      }

      .stat-card .label {
          font-size: 14px;
          opacity: 0.8;
      }




      /* Animations */
   
   

      @keyframes fadeInUp {
          from {
              opacity: 0;
              transform: translateY(40px);
          }
          to {
              opacity: 1;
              transform: translateY(0);
          }
      }


      /* Responsive */
      @media (max-width: 900px) {
          .profile-card {
              grid-column: span 1;
              flex-direction: column;
              text-align: center;
          }

          .profile-image-container {
              margin-right: 0;
              margin-bottom: 20px;
          }

          .profile-stats {
              justify-content: center;
          }

          .content-card {
              grid-column: span 1;
          }
      }

      @media (max-width: 600px) {
          .grid {
              grid-template-columns: 1fr;
          }

          .profile-stats {
              flex-direction: column;
              gap: 10px;
          }

          .stats-grid {
              grid-template-columns: 1fr;
          }
      }

      
.ultra-filter {
    background: rgba(255,255,255,0.9);
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.live-dot {
    width: 10px;
    height: 10px;
    background: #28a745;
    border-radius: 50%;
    display: inline-block;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.3; }
    100% { opacity: 1; }
}

.kpi-live {
    background: linear-gradient(135deg, #ab2b22, #fba919);
    color: #fff;
    padding: 15px;
    border-radius: 15px;
}

.kpi-live.green { background: linear-gradient(135deg,#28a745,#5ddf85); }
.kpi-live.orange { background: linear-gradient(135deg,#ff9800,#ffc107); }
.kpi-live.red { background: linear-gradient(135deg,#dc3545,#ff6b6b); }
.kpi-live.blue { background: linear-gradient(135deg,#007bff,#5bc0de); }
.kpi-live.purple { background: linear-gradient(135deg,#6f42c1,#b197fc); }

.alert-item, .activity-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.pipeline {
    display: flex;
    justify-content: space-between;
}
.pipeline div {
    background: #fff;
    padding: 10px;
    border-radius: 10px;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function loadDashboard() {

    let company = $('#companyFilter').val();
    let entity = $('#entityFilter').val();
    let department = $('#departmentFilter').val();
    const loader = document.getElementById('loadingScreenCustomize');
    
    $.ajax({
        url: "{{ route('hr.dashboard.data') }}",
        type: "GET",
        data: {
            company_id: company,
            entity_id: entity,
            department_id: department
        },
        beforeSend: function() {
           loader.style.display = 'flex';
        },
        success: function(res) {

            if(res.status) {

                let d = res.data;

                // 🔥 KPI UPDATE
                $('#totalStaff').text(d.totalStaff);
                $('#present').text(d.present);
                $('#late').text(d.late);
                $('#permission').text(d.permission);
                $('#absent').text(d.absent);

                renderList('#lateList', d.lateDetails);
                renderList('#absentList', d.absentDetails);
                renderList('#permissionList', d.permissionDetails);
                renderEntityCounts(d.entityCounts);
                // 🚨 ALERTS
                $('#alertsContainer').html(
                    d.alerts.length === 0 
                    ? '<div class="empty">No alerts ✅</div>'
                    : d.alerts.map(a => `
                        <div class="alert-box">
                            ${a.title}
                            <span>${a.time}</span>
                        </div>
                    `).join('')
                );

                // 📡 ACTIVITY
                $('#activityContainer').html(
                    d.activities.map(a => `<div class="activity-item">⚡ ${a}</div>`).join('')
                );

             

                 updateCharts(d);
                generateAIInsights(d);

             

               
            }
        },
        complete: function() {
          loader.style.display = 'none';
        }
    });
}

// 🔄 AUTO LOAD
loadDashboard();
$('#companyFilter, #entityFilter, #departmentFilter').on('change', loadDashboard);

function generateAIInsights(d) {

    let insights = '';

    // if(d.absent > (d.totalStaff * 0.2)) {
    //     insights += `<p>⚠️ High absenteeism detected today (${d.absent})</p>`;
    // }

    if(d.absent > (d.totalStaff * 0.2)) {
        insights += `🚨 Critical: High absenteeism<br>`;
    }

    if(d.late > 10) {
        insights += `<p>⏰ Many employees are late today (${d.late})</p>`;
    }

    if(d.hired > d.shortlisted) {
        insights += `<p>🚀 Hiring efficiency improved this week</p>`;
    }

    if(d.payroll > 1000000) {
        insights += `<p>💰 Payroll cost is high this month</p>`;
    }

    if(insights === '') {
        insights = `<p>✅ Everything looks normal today</p>`;
    }

    $('#aiInsights').html(insights);
}


function renderList(container, data, type = '') {
    if (!data || data.length === 0) {
        $(container).html('<div class="empty">No records 🎉</div>');
        return;
    }

    $(container).html(
        data.map(e => `
            <div class="mini-item">
                <div class="avatar">${e.staff_name.charAt(0)}</div>
                <div class="info">
                    <div class="name">${e.staff_name}</div>
                    ${e.in_time ? `<small>${e.in_time}</small>` : ''}
                </div>
            </div>
        `).join('')
    );
}

function renderEntityCounts(data) {
    if (!data || data.length === 0) return;

    $('#entityCounts').html(
        data.map(e => `
            <div class="entity-box" style="border-left:4px solid ${e.entity_base_color}">
                
                <div class="entity-top">
                    <span class="entity-name">${e.entity_short_name ?? e.entity_name}</span>
                    <span class="entity-company">${e.company_short_name ?? ''}</span>
                </div>

                <div class="entity-count">${e.total}</div>

            </div>
        `).join('')
    );
}
// 🔄 AUTO REFRESH EVERY 30 SEC (REAL-TIME FEEL)
// setInterval(loadDashboard, 30000);
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let attendanceChart;

function updateCharts(d) {

    let chartData = [
        d.present,
        d.absent,
        d.late,
        d.permission
    ];

    if(attendanceChart) {
        attendanceChart.destroy();
    }

    attendanceChart = new Chart(document.getElementById('attendanceChart'), {
        type: 'bar',
        data: {
            labels: ['Present','Absent','Late','Permission'],
            datasets: [{
                label: 'Today Status',
                data: chartData,
                borderWidth: 1
            }]
        }
    });
}
</script>
@endsection