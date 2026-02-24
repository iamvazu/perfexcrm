<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Urban Ladder CRM Customizations
 * Maintained via my_functions_helper.php
 */

hooks()->add_action('after_render_top_search', 'ul_maybe_create_dummy_leads');

function ul_maybe_create_dummy_leads() {
    if (get_instance()->input->get('create_dummy_leads') == '1') {
        $CI =& get_instance();
        $CI->load->database();
        
        $names = ["Aditya Sharma", "Priya Nair", "Rahul Verma", "Sneha Gupta", "Vikram Singh", "Ananya Das", "Rohan Mehta", "Ishani Kapoor", "Karan Malhotra", "Zoya Khan"];
        $cities = ["Bangalore", "Mumbai", "Delhi", "Pune", "Hyderabad"];
        $marketing_sources = ["Instagram Ads", "Google Search", "Walk-in", "Referral", "Newspaper"];
        $genders = ["Male", "Female", "Prefer not to say"];
        $ages = ["18-25", "26-35", "36-45", "46-55", "55+"];
        
        // Get first available source and status
        $source = $CI->db->get(db_prefix().'leads_sources')->row();
        $status = $CI->db->get(db_prefix().'leads_status')->row();
        
        if (!$source || !$status) return;

        for ($i = 0; $i < 10; $i++) {
            $lead = [
                'name'            => $names[$i],
                'title'           => 'Property Inquiry',
                'company'         => 'N/A',
                'description'     => 'Sample lead for testing Urban Ladder customization.',
                'country'         => 102, // India
                'city'            => $cities[array_rand($cities)],
                'address'         => ($i+1) . ' Main Street',
                'email'           => strtolower(str_replace(' ', '.', $names[$i])) . '@example.com',
                'phonenumber'     => '98765' . rand(10000, 99999),
                'source'          => $source->id,
                'status'          => $status->id,
                'dateadded'       => date('Y-m-d H:i:s'),
                'assigned'        => 1,
                'cx_age'           => $ages[array_rand($ages)],
                'gender'           => $genders[array_rand($genders)],
                'marketing_source' => $marketing_sources[array_rand($marketing_sources)],
                'qualified_date'   => date('Y-m-d', strtotime('-' . rand(0, 5) . ' days')),
            ];
            $CI->db->insert(db_prefix().'leads', $lead);
        }
        echo "<script>alert('10 Dummy Leads Created!');</script>";
    }
}

// 1. Database Update Hook - Automatically runs once
hooks()->add_action('admin_init', 'ul_db_update');

function ul_db_update() {
    $CI =& get_instance();
    if (get_option('ul_db_installed') == '1') {
        return;
    }

    $CI->load->dbforge();
    $prefix = db_prefix();

    $fields = [
        'idc_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE],
        'cx_age' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE],
        'gender' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => TRUE],
        'property_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE],
        'property_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE],
        'handover_month' => ['type' => 'DATE', 'null' => TRUE],
        'property_location' => ['type' => 'TEXT', 'null' => TRUE],
        'bhk' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE],
        'scope_of_work' => ['type' => 'TEXT', 'null' => TRUE],
        'budget_range' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE],
        'lead_source' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE],
        'meeting_date' => ['type' => 'DATETIME', 'null' => TRUE],
        'meeting_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE],
        'experience_centre' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE],
        'floor_plan_available' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        'meeting_status' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'default' => 'Pending'],
        'booking_value' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
        'next_action_date' => ['type' => 'DATE', 'null' => TRUE],
        'psa_agent_id' => ['type' => 'INT', 'null' => TRUE],
        'lead_score' => ['type' => 'INT', 'default' => 0],
        'qualified_date' => ['type' => 'DATE', 'null' => TRUE],
        'marketing_source' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => TRUE],
    ];

    foreach ($fields as $field_name => $field_data) {
        if (!$CI->db->field_exists($field_name, $prefix . 'leads')) {
            $CI->dbforge->add_column('leads', [$field_name => $field_data]);
        }
    }

    add_option('ul_db_installed', '1');
}

// 2. Lead Scoring Logic
hooks()->add_filter('before_lead_added', 'ul_process_lead_scoring');
hooks()->add_filter('before_lead_updated', 'ul_process_lead_scoring');

function ul_process_lead_scoring($data) {
    $score = 0;
    
    // Scoring based on Budget
    if (isset($data['budget_range'])) {
        if (strpos($data['budget_range'], '50+') !== false) $score += 40;
        elseif (strpos($data['budget_range'], '30-50') !== false) $score += 30;
        elseif (strpos($data['budget_range'], '20-30') !== false) $score += 20;
    }
    
    // Scoring based on Design Readiness
    if (isset($data['floor_plan_available']) && ($data['floor_plan_available'] == 1 || $data['floor_plan_available'] == 'on')) {
        $score += 20;
    }
    
    // Scoring based on Property Type
    if (isset($data['property_type']) && $data['property_type'] == 'Villa') {
        $score += 15;
    }
    
    // Process Scope of Work multi-select if it's an array
    if (isset($data['scope_of_work']) && is_array($data['scope_of_work'])) {
        if (in_array('Full Home Design', $data['scope_of_work'])) $score += 25;
        $data['scope_of_work'] = implode(',', $data['scope_of_work']);
    }

    $data['lead_score'] = $score;
    return $data;
}

// 3. Automation: Create Project on Booking
hooks()->add_action('lead_status_changed', 'ul_create_project_on_booking');

function ul_create_project_on_booking($data) {
    $CI =& get_instance();
    $lead_id = $data['lead_id'];
    
    // Fetch status name to check for "Booked"
    $status = $CI->leads_model->get_status($data['new_status']);
    
    if ($status && strtolower($status->name) == 'booked') {
        $lead = $CI->leads_model->get($lead_id);
        
        // Ensure lead is converted to client
        $CI->db->where('leadid', $lead_id);
        $customer = $CI->db->get(db_prefix() . 'clients')->row();
        
        if ($customer) {
            $CI->load->model('projects_model');
            
            // Check if project already exists
            $CI->db->where('clientid', $customer->userid);
            $CI->db->where('name', $customer->company . ' - Interior Design Project');
            if ($CI->db->count_all_results(db_prefix() . 'projects') == 0) {
                $project_data = [
                    'name' => $customer->company . ' - Interior Design Project',
                    'clientid' => $customer->userid,
                    'start_date' => date('Y-m-d'),
                    'status' => 1, // Not Started
                    'progress_from_tasks' => 1,
                    'project_members' => [$lead->assigned]
                ];
                
                $project_id = $CI->projects_model->add($project_data);
                
                if ($project_id) {
                    log_activity('Urban Ladder: Project automatically created from Lead Booking [LeadID: ' . $lead_id . ']');
                }
            }
        }
    }
}

// 4. Sales Dashboard Customization
hooks()->add_filter('before_dashboard_render', 'ul_dashboard_inject_stats');
hooks()->add_filter('get_dashboard_widgets', 'ul_add_dashboard_widgets');

function ul_add_dashboard_widgets($widgets) {
    $widgets[] = [
        'path'      => 'admin/dashboard/widgets/ul_sales_dashboard',
        'container' => 'top-12',
    ];
    return $widgets;
}

function ul_dashboard_inject_stats($data) {
    $CI =& get_instance();
    $cache_option = 'ul_dashboard_stats_cache';
    $cache_time_option = 'ul_dashboard_stats_cache_time';
    
    $cached_data = get_option($cache_option);
    $cache_time = get_option($cache_time_option);
    $now = time();

    if ($cached_data && $cache_time && ($now - $cache_time) < 600) {
        $inject = unserialize($cached_data);
    } else {
        $inject = [
            'ul_leads_by_source' => json_encode(ul_get_leads_by_source_stats()),
            'ul_property_type_stats' => json_encode(ul_get_property_type_distribution_stats()),
            'ul_budget_stats' => json_encode(ul_get_budget_range_analysis_stats()),
            'ul_agent_performance' => json_encode(ul_get_agent_performance_stats())
        ];
        // Cache for 10 minutes (600 seconds)
        if (!add_option($cache_option, serialize($inject), 0)) {
            update_option($cache_option, serialize($inject));
        }
        if (!add_option($cache_time_option, $now, 0)) {
            update_option($cache_time_option, $now);
        }
    }
    
    $CI->load->vars($inject);
    return array_merge($data, $inject);
}

function ul_get_leads_by_source_stats() {
    $CI =& get_instance();
    if (!$CI->db->field_exists('lead_source', db_prefix().'leads')) {
        return ['labels' => [], 'datasets' => [['data' => []]]];
    }
    $CI->db->select('lead_source as name, COUNT(*) as total');
    $CI->db->from(db_prefix() . 'leads');
    $CI->db->where('lead_source IS NOT NULL AND lead_source != ""');
    $CI->db->group_by('lead_source');
    $result = $CI->db->get()->result_array();

    $chart = ['labels' => [], 'datasets' => [['data' => [], 'backgroundColor' => []]]];
    $colors = ['#2563eb', '#9333ea', '#db2777', '#ea580c', '#16a34a', '#ca8a04'];
    
    foreach ($result as $i => $row) {
        $chart['labels'][] = $row['name'];
        $chart['datasets'][0]['data'][] = (int)$row['total'];
        $chart['datasets'][0]['backgroundColor'][] = $colors[$i % count($colors)];
    }
    return $chart;
}

function ul_get_property_type_distribution_stats() {
    $CI =& get_instance();
    if (!$CI->db->field_exists('property_type', db_prefix().'leads')) {
        return ['labels' => [], 'datasets' => [['data' => []]]];
    }
    $CI->db->select('property_type as name, COUNT(*) as total');
    $CI->db->from(db_prefix() . 'leads');
    $CI->db->where('property_type IS NOT NULL AND property_type != ""');
    $CI->db->group_by('property_type');
    $result = $CI->db->get()->result_array();

    $chart = ['labels' => [], 'datasets' => [['data' => [], 'backgroundColor' => ['#0284c7', '#f59e0b', '#10b981']]]];
    foreach ($result as $row) {
        $chart['labels'][] = $row['name'];
        $chart['datasets'][0]['data'][] = (int)$row['total'];
    }
    return $chart;
}

function ul_get_budget_range_analysis_stats() {
    $CI =& get_instance();
    if (!$CI->db->field_exists('budget_range', db_prefix().'leads')) {
        return ['labels' => [], 'datasets' => [['data' => []]]];
    }
    $CI->db->select('budget_range as name, COUNT(*) as total');
    $CI->db->from(db_prefix() . 'leads');
    $CI->db->where('budget_range IS NOT NULL AND budget_range != ""');
    $CI->db->group_by('budget_range');
    $result = $CI->db->get()->result_array();

    $chart = ['labels' => [], 'datasets' => [['data' => [], 'backgroundColor' => '#6366f1', 'label' => 'Leads by Budget']]];
    foreach ($result as $row) {
        $chart['labels'][] = $row['name'];
        $chart['datasets'][0]['data'][] = (int)$row['total'];
    }
    return $chart;
}

function ul_get_agent_performance_stats() {
    $CI =& get_instance();
    $CI->db->select('CONCAT(firstname, " ", lastname) as agent, COUNT('.db_prefix().'leads.id) as assigned, SUM(CASE WHEN status = (SELECT id FROM '.db_prefix().'leads_status WHERE name LIKE "%Booked%" LIMIT 1) THEN 1 ELSE 0 END) as booked');
    $CI->db->from(db_prefix() . 'staff');
    $CI->db->join(db_prefix() . 'leads', db_prefix() . 'leads.assigned = ' . db_prefix() . 'staff.staffid', 'left');
    $CI->db->where('is_not_staff', 0);
    $CI->db->group_by(db_prefix() . 'staff.staffid');
    $result = $CI->db->get()->result_array();

    $chart = [
        'labels' => array_column($result, 'agent'),
        'datasets' => [
            [
                'label' => 'Assigned',
                'backgroundColor' => '#94a3b8',
                'data' => array_column($result, 'assigned')
            ],
            [
                'label' => 'Booked',
                'backgroundColor' => '#22c55e',
                'data' => array_column($result, 'booked')
            ]
        ]
    ];
    return $chart;
}

// 5. Menu Customization
hooks()->add_filter('sidebar_menu_items', 'ul_customize_sidebar_menu');

function ul_customize_sidebar_menu($items) {
    // Remove unnecessary modules
    $remove_items = ['subscriptions', 'expenses', 'contracts'];
    foreach ($remove_items as $item_key) {
        if (isset($items[$item_key])) {
            unset($items[$item_key]);
        }
    }

    // Move Leads to 3rd place (position 8, between Customers at 5 and Sales at 10)
    if (isset($items['leads'])) {
        $items['leads']['position'] = 8;
    }
    
    return $items;
}

// 6. Logo & Icon Customization
hooks()->add_filter('admin_header_logo_url', 'ul_custom_admin_logo');
hooks()->add_filter('company_logo', 'ul_custom_company_logo');
hooks()->add_action('app_admin_head', 'ul_add_favicon');
hooks()->add_action('app_client_head', 'ul_add_favicon');

function ul_custom_admin_logo($url) {
    return base_url('uploads/company/urban_ladder_logo.jpg?v=2');
}

function ul_custom_company_logo($logo) {
    if (!function_exists('get_instance')) return $logo;
    $CI =& get_instance();
    $class = strtolower($CI->router->fetch_class());
    
    // The previous implementation showed the icon on the login page, 
    // indicating that the filenames were likely swapped or the user wants the other image.
    if ($class == 'authentication' || ($class == 'clients' && strtolower($CI->router->fetch_method()) == 'login')) {
        return '<a href="' . site_url() . '" class="navbar-brand logo img-responsive">
            <img src="' . base_url('uploads/company/urban_ladder_icon.png?v=2') . '" class="img-responsive" alt="Urban Ladder" style="max-height: 70px; margin-top: -15px;">
        </a>';
    }
    
    // For other places, return the square icon if no logo is set
    if (empty($logo) || strip_tags($logo) == get_option('companyname')) {
        return '<a href="' . site_url() . '" class="logo img-responsive">
            <img src="' . base_url('uploads/company/urban_ladder_logo.jpg?v=2') . '" class="img-responsive" alt="Urban Ladder" style="max-height: 40px;">
        </a>';
    }

    return $logo;
}

function ul_add_favicon() {
    echo '<link rel="shortcut icon" href="' . base_url('uploads/company/urban_ladder_icon.png') . '" type="image/png">';
}
