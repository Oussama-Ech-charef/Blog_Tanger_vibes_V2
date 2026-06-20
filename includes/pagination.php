<?php

/**
 * @param string $param
 * @return int
 */
function get_valid_page($param = 'page') {
    $page = isset($_GET[$param]) ? (int)$_GET[$param] : 1;
    return max(1, $page);
}

/**
 * @param int $page
 * @param int $per_page
 * @return int
 */
function get_offset($page, $per_page) {
    return ($page - 1) * $per_page;
}

/**
 * @param int $total_records
 * @param int $per_page
 * @return int
 */
function get_total_pages($total_records, $per_page) {
    return max(1, (int)ceil($total_records / $per_page));
}

/**
 * @param int $current_page
 * @param int $total_pages
 * @param array $query_params
 * @return string
 */
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
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> ' . __('pagination_prev') . '
        </a>';
    } else {
        $html .= '<span class="page_btn prev disabled">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i> ' . __('pagination_prev') . '
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
            ' . __('pagination_next') . ' <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </a>';
    } else {
        $html .= '<span class="page_btn next disabled">
            ' . __('pagination_next') . ' <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </span>';
    }

    $html .= '</div>';

    return $html;
}
