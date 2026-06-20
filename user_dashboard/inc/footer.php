        </div><!-- /.dashboard_content -->
    </div><!-- /.dashboard_main -->
</div><!-- /.dashboard_layout -->

<div class="modal_overlay" id="postQuickView">
    <div class="modal_box" style="width:min(100%,600px);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="margin:0;font-size:18px;font-weight:700;" id="qvTitle"></h2>
            <button class="alert_close" id="qvClose" style="font-size:22px;cursor:pointer;background:none;border:none;color:var(--db-text-muted);padding:4px 8px;">&times;</button>
        </div>
        <div id="qvImage" style="margin-bottom:16px;display:none;">
            <img src="" alt="" style="width:100%;max-height:280px;object-fit:cover;border-radius:8px;border:1px solid var(--db-card-border);">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;font-size:14px;">
            <div><span class="detail_label" style="display:block;margin-bottom:2px;">Category</span><span id="qvCategory" style="font-weight:600;"></span></div>
            <div><span class="detail_label" style="display:block;margin-bottom:2px;">Status</span><span id="qvStatus"></span></div>
            <div><span class="detail_label" style="display:block;margin-bottom:2px;">Author</span><span id="qvAuthor" style="font-weight:600;"></span></div>
            <div><span class="detail_label" style="display:block;margin-bottom:2px;">Created</span><span id="qvDate" style="font-weight:600;"></span></div>
        </div>
        <div id="qvRejection" style="display:none;background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#991B1B;">
            <strong style="display:block;margin-bottom:4px;color:#EF4444;">Rejection Reason</strong>
            <span id="qvRejectionText"></span>
        </div>
        <div>
            <span class="detail_label" style="display:block;margin-bottom:6px;">Content</span>
            <div id="qvContent" style="font-size:14px;line-height:1.7;color:var(--db-text-primary);white-space:pre-wrap;max-height:300px;overflow-y:auto;background:#F8FAFC;padding:16px;border-radius:8px;border:1px solid var(--db-card-border);"></div>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
</body>
</html>
