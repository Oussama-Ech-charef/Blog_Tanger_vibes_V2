<?php

function get_valid_page($param = 'page') {
    $page = isset($_GET[$param]) ? (int)$_GET[$param] : 1;
    return max(1, $page);
}

function get_offset($page, $per_page) {
    return ($page - 1) * $per_page;
}

function get_total_pages($total_records, $per_page) {
    return max(1, (int)ceil($total_records / $per_page));
}

function render_pagination($current_page, $total_pages, $query_params = []) {
    if ($total_pages <= 1) {
        return '';
    }

    $html = '<div class="pagination">';

    // Prev
    if ($current_page > 1) {
        $params = $query_params;
        $params['page'] = $current_page - 1;
        $html .= '<a href="?' . http_build_query($params) . '" class="page_btn prev">
            <i class="fa-solid fa-chevron-left"></i> Prev
        </a>';
    } else {
        $html .= '<span class="page_btn prev disabled">
            <i class="fa-solid fa-chevron-left"></i> Prev
        </span>';
    }

    // Page numbers with window
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);

    if ($start > 1) {
        $params = $query_params;
        $params['page'] = 1;
        $html .= '<a href="?' . http_build_query($params) . '" class="page_btn">1</a>';
        if ($start > 2) {
            $html .= '<span class="page_btn dots">...</span>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $params = $query_params;
        $params['page'] = $i;
        $active = $i == $current_page ? ' active' : '';
        $html .= '<a href="?' . http_build_query($params) . '" class="page_btn' . $active . '">' . $i . '</a>';
    }

    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $html .= '<span class="page_btn dots">...</span>';
        }
        $params = $query_params;
        $params['page'] = $total_pages;
        $html .= '<a href="?' . http_build_query($params) . '" class="page_btn">' . $total_pages . '</a>';
    }

    // Next
    if ($current_page < $total_pages) {
        $params = $query_params;
        $params['page'] = $current_page + 1;
        $html .= '<a href="?' . http_build_query($params) . '" class="page_btn next">
            Next <i class="fa-solid fa-chevron-right"></i>
        </a>';
    } else {
        $html .= '<span class="page_btn next disabled">
            Next <i class="fa-solid fa-chevron-right"></i>
        </span>';
    }

    $html .= '</div>';

    return $html;
}
