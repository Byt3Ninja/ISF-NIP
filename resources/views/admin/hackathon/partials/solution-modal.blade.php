<div class="modal fade" id="solutionDetailsModal" tabindex="-1" aria-labelledby="solutionDetailsModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="solutionDetailsModalLabel">
          {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h4 id="modalTeamName">{{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}</h4>
        <h6 id="modalProjectName" class="text-muted mb-3">{{ $l == 'en' ? 'Project Name' : 'اسم المشروع' }}</h6>
        <p><strong>{{ $l == 'en' ? 'Description:' : 'الوصف:' }}</strong></p>
        <p id="modalProjectDescription">
          {{ $l == 'en' ? 'This is a detailed description of the solution provided by the team. It explains the
          problem being addressed, the technologies used, and the expected outcomes...' : 'هذا وصف تفصيلي للحل المقدم
          من الفريق. يشرح المشكلة التي يعالجها، والتقنيات المستخدمة، والنتائج المتوقعة...' }}
        </p>
        <p>
          <strong>{{ $l == 'en' ? 'Technologies Used:' : 'التقنيات المستخدمة:' }}</strong>
          Python, TensorFlow, React, AWS
        </p>
        <p>
          <strong>{{ $l == 'en' ? 'Presentation Link:' : 'رابط العرض التقديمي:' }}</strong>
          <a href="#" target="_blank">{{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}</a>
        </p>
        <p>
          <strong>{{ $l == 'en' ? 'Code Repository Link:' : 'رابط مستودع الكود:' }}</strong>
          <a href="#" target="_blank">{{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}</a>
        </p>
        <img src="https://via.placeholder.com/700x400.png?text=Project+Screenshot" class="img-fluid rounded mt-3"
          alt="Screenshot">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ $l == 'en' ? 'Close' : 'إغلاق' }}
        </button>
      </div>
    </div>
  </div>
</div>