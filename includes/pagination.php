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

/**
 * @param string $base_url
 * @param int $current_page
 * @param int $total_pages
 * @param array $query_params
 * @param int $per_page
 * @param int $total_records
 * @return void
 */
function render_dashboard_pagination($base_url, $current_page, $total_pages, $query_params = [], $per_page = 0, $total_records = 0) {
    if ($total_pages <= 1) return;

    $start_record = ($current_page - 1) * $per_page + 1;
    $end_record = min($current_page * $per_page, $total_records);

    $u = http_build_query($query_params);
    $sep = !empty($u) ? '?' . $u . '&page=' : '?page=';
    $prev_disabled = $current_page <= 1 ? ' disabled' : '';
    $next_disabled = $current_page >= $total_pages ? ' disabled' : '';
    ?>
    <div class="pagination_wrapper">
        <?php if ($total_records > 0 && $per_page > 0): ?>
        <div class="pagination_info"><?= __('pagination_showing', $start_record, $end_record, $total_records) ?></div>
        <?php endif; ?>
        <div class="dashboard_pagination">
            <a href="<?= $base_url . $sep . ($current_page - 1) ?>" class="page_btn prev<?= $prev_disabled ?>" aria-label="<?= __('pagination_prev_aria') ?>"<?= $prev_disabled ? ' tabindex="-1" aria-disabled="true"' : '' ?>><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>

            <?php
            $window = 2;
            $start = max(1, $current_page - $window);
            $end = min($total_pages, $current_page + $window);

            if ($start > 1): ?>
                <a href="<?= $base_url . $sep . 1 ?>" class="page_btn<?= $current_page === 1 ? ' active' : '' ?>">1</a>
                <?php if ($start > 2): ?><span class="page_btn dots">...</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?= $base_url . $sep . $i ?>" class="page_btn<?= $i === $current_page ? ' active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span class="page_btn dots">...</span><?php endif; ?>
                <a href="<?= $base_url . $sep . $total_pages ?>" class="page_btn<?= $current_page === $total_pages ? ' active' : '' ?>"><?= $total_pages ?></a>
            <?php endif; ?>

            <a href="<?= $base_url . $sep . ($current_page + 1) ?>" class="page_btn next<?= $next_disabled ?>" aria-label="<?= __('pagination_next_aria') ?>"<?= $next_disabled ? ' tabindex="-1" aria-disabled="true"' : '' ?>><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
        </div>
    </div>
    <?php
}
