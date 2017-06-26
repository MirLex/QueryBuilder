<?php
namespace app;
class QueryBuilder {
  
  private $dictionary     = array();
  private $words          = array();
  private $sequences      = array();
  private $query          = array();

  public function __construct($user_query,$dictionary_link = 'dictionary.yaml', $max_sequence = 2){
    $this->max_sequence = $max_sequence;
    $this->dictionary = yaml_parse_file("data/" . $dictionary_link);
    $this->get_words(trim($user_query));
    $this->get_sequence($this->words);
    $this->prepare_query();
  }

  private function get_words($str) {
    $words = preg_split("/[\s,]+/", trim($str));
    foreach ($words as $word) {
      if (strlen(utf8_decode($word)) >= 2) {
        $this->words[] = mb_strtolower($word,'UTF-8');
      }
    }
  }

  private function get_sequence($words){
    for ($word_idx=0; $word_idx < count($words); $word_idx++) { 
      for ($seq_idx=$word_idx; $seq_idx <= $word_idx+$this->max_sequence; $seq_idx++) { 
        $curr_seq = array_slice($words, $word_idx, $seq_idx - $word_idx);
        if (count($curr_seq) > 1) {
          $key_word = implode(" ", $curr_seq);
          if (isset($this->dictionary[$key_word])) {
            $seq['key_word'] = $key_word;
            $seq['start'] = $word_idx;
            $seq['end'] = $seq_idx-1;
            $this->sequences[] = $seq;
          }
        }
      }
    }
  }

  private function get_synonyms($key_word) {
    if (isset($this->dictionary[mb_strtolower($key_word)])) {
      return implode('|', $this->dictionary[$key_word]);
    }
    return false;
  }

  private function prepare_query() {
    for ($word_idx=0; $word_idx <count($this->words); $word_idx++) { 
      $this->query[$word_idx]['word'] = $this->words[$word_idx];
      foreach ($this->sequences as $seq_key => $seq) {
        if ($seq['start'] == $word_idx) {
          $this->query[$word_idx]['seqs'][] = array('seq_key' => $seq_key, 'start' => $seq['start']);
        }
        if ($seq['end'] == $word_idx) {
          $this->query[$word_idx]['seqs'][] = array('seq_key' => $seq_key, 'end' => $seq['end']);
        } 
      }
    }
  }

  public function get_query() {
    $result = '';
    for ($i=0; $i <count($this->query); $i++) { 
      if (isset($this->query[$i]['seqs'])) {
          foreach ($this->query[$i]['seqs'] as $seq) {
            if (isset($seq['start'])) {
              $result .= '(';
            } 
          }
      }
   
      if (isset($this->dictionary[$this->query[$i]['word']])) {
        $result .= $this->query[$i]['word']."|".$this->get_synonyms($this->query[$i]['word']);
      } else {
        $result .= $this->query[$i]['word'];
      }

      if (isset($this->query[$i]['seqs'])) {
          foreach ($this->query[$i]['seqs'] as $seq) {
            if (isset($seq['end'])) {
              $result .= ')';
              $result .= "|" . $this->get_synonyms($this->sequences[$seq['seq_key']]['key_word']);
            } 
          }
      }

      if ($i < count($this->query)-1) {
        $result .= ' & ';
      } 
    }
    return $result;
  }
}

?>