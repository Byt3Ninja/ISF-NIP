<?php
$categories = [
  ['title' => 'Ownergroup & Board', 'answered' => 1, 'status' => 'completed'],
  ['title' => 'Employees', 'answered' => 2, 'status' => 'in-progress'],
  ['title' => 'Business Processes', 'answered' => 3, 'status' => 'not-started'],
  ['title' => 'Partnerships', 'answered' => 4, 'status' => 'not-started'],
  ['title' => 'Legal Issues', 'answered' => 5, 'status' => 'not-started'],
];

$questions = [
  ['title' => 'Question 1 title for organization', 'category' => 'Ownergroup & Board', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for organization', 'category' => 'Ownergroup & Board', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for organization', 'category' => 'Ownergroup & Board', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for organization', 'category' => 'Ownergroup & Board', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for organization', 'category' => 'Ownergroup & Board', 'options' => ['label 1', 'label 2', 'label 3']],
  
  ['title' => 'Question 1 title for product-portfolio', 'category' => 'Employees', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for product-portfolio', 'category' => 'Employees', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for product-portfolio', 'category' => 'Employees', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for product-portfolio', 'category' => 'Employees', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for product-portfolio', 'category' => 'Employees', 'options' => ['label 1', 'label 2', 'label 3']],
 
  ['title' => 'Question 1 title for revenue-modal', 'category' => 'Business Processes', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for revenue-modal', 'category' => 'Business Processes', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for revenue-modal', 'category' => 'Business Processes', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for revenue-modal', 'category' => 'Business Processes', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for revenue-modal', 'category' => 'Business Processes', 'options' => ['label 1', 'label 2', 'label 3']],

  ['title' => 'Question 1 title for customer-portfolio', 'category' => 'Partnerships', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for customer-portfolio', 'category' => 'Partnerships', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for customer-portfolio', 'category' => 'Partnerships', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for customer-portfolio', 'category' => 'Partnerships', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for customer-portfolio', 'category' => 'Partnerships', 'options' => ['label 1', 'label 2', 'label 3']],

  ['title' => 'Question 1 title for market-segment', 'category' => 'Legal Issues', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for market-segment', 'category' => 'Legal Issues', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for market-segment', 'category' => 'Legal Issues', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for market-segment', 'category' => 'Legal Issues', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for market-segment', 'category' => 'Legal Issues', 'options' => ['label 1', 'label 2', 'label 3']],
];
?>

<style>
  .active-cat {
    border: 1px solid #000;
  }
</style>

<div class="d-flex flex-column gap-6">
  <div class="d-flex flex-wrap gap-2">
    @foreach($categories as $key => $category)
    <div 
      onclick="showOrganizationQuestions('{{ $category['title'] }}', {{ $key }});"
      class="card card-org shadow-sm cursor-pointer {{ $key == 0 ? 'active-cat' : '' }}" 
      id="org-category-tab-{{ $key }}"
    >
      <div class="px-3 py-5 d-flex flex-column gap-2 text-center">
        <span class="text-capitalize fw-bold text-sm">
          {{ str_replace('-', ' ', $category['title']) }}
        </span>
        @if ($key == 0)
          <span class="text-success fw-bold">
            Done All
          </span>
        @else
          <span class="text-secondary" style="font-size: 10px;">
            {{ $category['answered'] }}/5 questions answered
          </span>
        @endif
      </div>
    </div>
    @endforeach
  </div>
  <div class="row">
    @foreach($questions as $key => $question)
    <div 
      class="col-12 question-card-org mb-3"
      data-category="{{ $question['category'] }}"
      style="display: {{ $question['category'] == 'Ownergroup & Board' ? 'block' : 'none' }}"
    >
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-2 pb-2 border-bottom">
            <span
              class="badge bg-primary rounded-circle d-flex justify-content-center align-items-center"
              style="width: 30px; height: 30px;"
            >
              {{ $key + 1 }}
            </span>
            <h6 class="fw-bold mb-0">{{ $question['title'] }}</h6>
          </div>
          <div class="mt-3">
            @foreach($question['options'] as $option)
            <div class="form-check">
              <input class="form-check-input" type="radio" name="{{ $question['title'] }}" id="{{ $option }}">
              <label class="form-check-label" for="{{ $option }}">
                {{ $option }}
              </label>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <div class="d-flex justify-content-between">
    {{-- <button class="btn btn-secondary btn-prev" onclick="changeOrgCategory(-1)">Prev</button> --}}
    {{-- <button class="btn btn-primary btn-next-org ms-auto" >Submit</button> --}}
    <button class="btn btn-primary btn-next-org ms-auto" onclick="changeOrgCategory(1)">Submit</button>
  </div>
</div>

<script>
  let currentCategoryIndexForOrg = 0;

  const showOrganizationQuestions = (category, index) => {
    console.log(document.querySelectorAll('.question-card-org'))
    document.querySelectorAll('.question-card-org').forEach(card => {
      card.style.display = card.getAttribute('data-category') === category ? 'block' : 'none';
    });
    document.querySelectorAll('.card.card-org').forEach(card => {
      card.classList.remove('active-cat');
    });
    document.getElementById(`org-category-tab-${index}`).classList.add('active-cat');
    currentCategoryIndexForOrg = index;
    updateNavigationButtonsForOrg();
  }

  const changeOrgCategory = (direction) => {
    const categories = @json($categories);
    let newIndex = currentCategoryIndexForOrg + direction;
    if (newIndex < 0) newIndex = categories.length - 1;
    if (newIndex >= categories.length) newIndex = 0;
    showOrganizationQuestions(categories[newIndex].title, newIndex);
  }

  // const updateNavigationButtonsForOrg = () => {
  //   const categories = @json($categories);
  //   console.log('currentCategoryIndexForOrg', currentCategoryIndexForOrg)
  //   let curIndex = currentCategoryIndexForOrg;
  //   // document.querySelector('.btn-prev').classList.add(curIndex === 0 ? 'd-none' : 'd-block')
  //   document.querySelector('.btn-next-org').classList.add(curIndex === categories.length - 1 ? 'd-none' : 'd-block')
  // }

  // document.addEventListener('DOMContentLoaded', () => {
  //   updateNavigationButtonsForOrg();
  // });
</script>


