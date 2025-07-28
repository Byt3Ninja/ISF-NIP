<div id="countdown-timer-container" class="alert text-center mb-4"
  style="display:none; background: linear-gradient(45deg, #17a2b8, #20c997); border: none; border-radius: 15px; color: white; box-shadow: 0 8px 25px rgba(23, 162, 184, 0.3);">
  <div class="d-flex align-items-center justify-content-center">
    <i class="ti ti-clock-hour-4 me-2" style="font-size: 1.5rem; me-2"></i>
    <strong>
      {{ $l == 'en' ? 'Time remaining to start hackathon:' : 'الوقت المتبقي لبدء الهاكثون:' }}
    </strong>
  </div>
  <div class="mt-2">
    <span id="countdown-timer" class="countdown-timer"
      style="font-size: 1.8rem; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
  </div>
</div>

<div id="submission-countdown-timer-container" class="alert text-center mb-4"
  style="display:none; background: linear-gradient(45deg, #fd7e14, #ffc107); border: none; border-radius: 15px; color: white; box-shadow: 0 8px 25px rgba(253, 126, 20, 0.3);">
  <div class="d-flex align-items-center justify-content-center">
    <i class="ti ti-upload me-2" style="font-size: 1.5rem; me-2"></i>
    <strong>
      {{ $l == 'en' ? 'Time remaining to submit projects:' : 'الوقت المتبقي لتقديم المشاريع:' }}
    </strong>
  </div>
  <div class="mt-2">
    <span id="submission-countdown-timer" class="countdown-timer"
      style="font-size: 1.8rem; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
  </div>
</div>