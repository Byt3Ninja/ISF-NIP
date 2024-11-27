<?php
$categories = [
  ['title' => 'business-idea', 'answered' => 1, 'status' => 'completed'],
  ['title' => 'product-portfolio', 'answered' => 2, 'status' => 'in-progress'],
  ['title' => 'revenue-modal', 'answered' => 3, 'status' => 'not-started'],
  ['title' => 'customer-portfolio', 'answered' => 4, 'status' => 'not-started'],
  ['title' => 'market-segment', 'answered' => 5, 'status' => 'not-started'],
];

$questions = [
  ['title' => 'Question 1 title for business-idea', 'category' => 'business-idea', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for business-idea', 'category' => 'business-idea', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for business-idea', 'category' => 'business-idea', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for business-idea', 'category' => 'business-idea', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for business-idea', 'category' => 'business-idea', 'options' => ['label 1', 'label 2', 'label 3']],
  
  ['title' => 'Question 1 title for product-portfolio', 'category' => 'product-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for product-portfolio', 'category' => 'product-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for product-portfolio', 'category' => 'product-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for product-portfolio', 'category' => 'product-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for product-portfolio', 'category' => 'product-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
 
  ['title' => 'Question 1 title for revenue-modal', 'category' => 'revenue-modal', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for revenue-modal', 'category' => 'revenue-modal', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for revenue-modal', 'category' => 'revenue-modal', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for revenue-modal', 'category' => 'revenue-modal', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for revenue-modal', 'category' => 'revenue-modal', 'options' => ['label 1', 'label 2', 'label 3']],

  ['title' => 'Question 1 title for customer-portfolio', 'category' => 'customer-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for customer-portfolio', 'category' => 'customer-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for customer-portfolio', 'category' => 'customer-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for customer-portfolio', 'category' => 'customer-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for customer-portfolio', 'category' => 'customer-portfolio', 'options' => ['label 1', 'label 2', 'label 3']],

  ['title' => 'Question 1 title for market-segment', 'category' => 'market-segment', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 2 title for market-segment', 'category' => 'market-segment', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 3 title for market-segment', 'category' => 'market-segment', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 4 title for market-segment', 'category' => 'market-segment', 'options' => ['label 1', 'label 2', 'label 3']],
  ['title' => 'Question 5 title for market-segment', 'category' => 'market-segment', 'options' => ['label 1', 'label 2', 'label 3']],
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
      onclick="showQuestions('{{ $category['title'] }}');"
      class="card shadow-sm cursor-pointer {{ $key == 0 ? 'active-cat' : '' }}" 
      id="category-tab"
    >
      <div class="px-3 py-5 d-flex flex-column gap-2">
        <span class="text-capitalize fw-bold text-sm">
          {{ str_replace('-', ' ', $category['title']) }}
        </span>
        <span class="text-secondary" style="font-size: 10px;">
          {{ $category['answered'] }}/5 questions answered
        </span>
      </div>
    </div>
    @endforeach
  </div>
  <div class="row">
    @foreach($questions as $key => $question)
    <div 
      class="col-12 question-card mb-3"
      data-category="{{ $question['category'] }}"
      style="display: {{ $question['category'] == 'business-idea' ? 'block' : 'none' }}" 
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
</div>

