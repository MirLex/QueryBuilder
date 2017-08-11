<?php
namespace app;
class QueryBuilderTest extends \PHPUnit_Framework_TestCase {
   public function testQuery_1() {
        $instance = new QueryBuilder('Системный администратор в офис');
        $this->assertEquals('(системный|системний|system & администратор|админ.)|"системный администратор"|сисадмин|"systems administrator"|"DevOps engineer" & офис', $instance->get_query());
    }
    public function testQuery_2() {
        $instance = new QueryBuilder('Системный администратор баз данных и безопасности');
        $this->assertEquals('(системный|системний|system & администратор|админ.)|"системный администратор"|сисадмин|"systems administrator"|"DevOps engineer" & (баз & данных)|"баз данных"|БД|database & безопасности|безпеки|security', $instance->get_query());
    }
    public function testQuery_3(){
        $instance = new QueryBuilder('Системный администратор БД и безопасности');
        $this->assertEquals('(системный|системний|system & (администратор|админ.)|"системный администратор"|сисадмин|"systems administrator"|"DevOps engineer" & "баз данных"|БД|database)|"администратор баз данных"|"адміністратор БД"|"администратор БД"|"database administrator"|dba & безопасности|безпеки|security', $instance->get_query());
    }
}