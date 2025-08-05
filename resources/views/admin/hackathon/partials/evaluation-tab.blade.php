<div class="card">
  <div class="card-body">
    <h5 class="card-title">
      {{ $l == 'en' ? 'Evaluation Criteria' : 'معايير التقييم' }}
    </h5>
    <p>
      {{ $l == 'en' ? 'The judging committee will evaluate projects based on the following criteria:' : 'ستقوم
      لجنة التحكيم بتقييم المشاريع بناءً على المعايير التالية:' }}
    </p>
    <ul class="list-group list-group-flush" id="hackathon-evaluation-criteria">
      <li class="list-group-item">
        <strong>
          {{ $l == 'en' ? 'Innovation and Originality (30%):' : 'الابتكار والأصالة (30%):' }}
        </strong>
        {{ $l == 'en' ? 'How innovative the idea is and its ability to provide a unique solution.' : 'مدى حداثة
        الفكرة وقدرتها على تقديم حل فريد.' }}
      </li>
      <li class="list-group-item">
        <strong>
          {{ $l == 'en' ? 'Practical Application and Feasibility (25%):' : 'التطبيق العملي والقابلية للتنفيذ
          (25%):' }}
        </strong>
        {{ $l == 'en' ? 'The possibility of implementing the solution in reality and its potential impact.' :
        'إمكانية تطبيق الحل على أرض الواقع وتأثيره المحتمل.' }}
      </li>
      <li class="list-group-item">
        <strong>
          {{ $l == 'en' ? 'Quality of Prototype/Presentation (20%):' : 'جودة النموذج الأولي/العرض (20%):' }}
        </strong>
        {{ $l == 'en' ? 'How complete and effective the prototype is and how it is presented.' : 'مدى اكتمال
        وفعالية النموذج الأولي وطريقة عرضه.' }}
      </li>
      <li class="list-group-item">
        <strong>
          {{ $l == 'en' ? 'Business Model and Sustainability (15%):' : 'نموذج العمل والاستدامة (15%):' }}
        </strong>
        {{ $l == 'en' ? 'Clarity of the proposed business model and the project\'s ability to continue and
        grow.' : 'وضوح نموذج العمل المقترح وقدرة المشروع على الاستمرار والنمو.' }}
      </li>
      <li class="list-group-item">
        <strong>
          {{ $l == 'en' ? 'Team Performance and Collaboration (10%):' : 'أداء الفريق والتعاون (10%):' }}
        </strong>
        {{ $l == 'en' ? 'How effectively the team worked and collaborated during the hackathon.' : 'مدى فعالية
        عمل الفريق وتعاونه خلال الهاكثون.' }}
      </li>
    </ul>
  </div>
</div>