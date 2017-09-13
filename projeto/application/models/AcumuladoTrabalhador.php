<?php

class Application_Model_AcumuladoTrabalhador extends ValeCultura_Db_Table_Abstract {

    const COD_TOTAL       = 1;
    const COD_DATA        = 2;
    const COD_ATIVO       = 3;
    const COD_LOCALIZACAO = 4;
    const COD_FILTROS     = 7;

    private $table = null;
    private $name = 'ACUMULADO_TRABALHADOR';
    
    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_AcumuladoTrabalhador();
        }
        return $this->table;
    }

    public function getAtivosPorFiltro($filtros)
    {
        $select = $this->getTable()->select();
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS ano, DESCRICAO_2 AS mes, DESCRICAO_3 AS regiao, DESCRICAO_4 AS uf, valor'), 'BI_VALE_CULTURA');
        
        $colunas = array(
            'ano' => 'DESCRICAO_1',
            'mes' => 'DESCRICAO_2',
            'regiao' => 'DESCRICAO_3',
            'uf' => 'DESCRICAO_4'            
        );
        
        foreach ($filtros as $filtro => $valor) {
            if ($valor) {
                $select->where($colunas[$filtro] . ' = ?', $valor);
            }
        }
        $select->where('TIPO = ?', self::COD_FILTROS);
        
        return $this->getTable()->fetchAll($select)->toArray();
    }
       
    public function getTotal($tipo) {
        $select = $this->getTable()->select();
        $select->from($this->name, 'valor', 'BI_VALE_CULTURA');
        $codTipo = ($tipo == 'acumulados') ? self::COD_TOTAL : self::COD_ATIVO;
        $select->where('TIPO = ?', $codTipo);

        // caso especial: ativos atual é o valor registrado para o último mês
        if ($tipo == 'ativos') {
            $select->order(array(
                'DESCRICAO_1 DESC',
                'DESCRICAO_2 DESC'
            ));
            $select->limit(1);
        }
        
        return $this->getTable()->fetchAll($select)->toArray();
    }
    
    public function getPorData($tipo = null, $ano = null, $mes = null)
    {
        $select = $this->getTable()->select();
        $codTipo = ($tipo == 'acumulados') ? self::COD_DATA : self::COD_ATIVO;
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS ano, DESCRICAO_2 AS mes, valor'), 'BI_VALE_CULTURA');
        $select->where('TIPO = ?', $codTipo);
        $select->order(array(
            'DESCRICAO_1 ASC',
            'DESCRICAO_2 ASC'
        ));        
        
        if ($ano) {
            $select->where('DESCRICAO_1 = ?', $ano);
        }
        if ($mes) {
            $select->where('DESCRICAO_2 = ?', $mes);
        }
        
        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function getPorLocalizacao($regiao = null, $uf = null)
    {
        $select = $this->getTable()->select();
        $select->where('TIPO = ?', self::COD_LOCALIZACAO);
        $select->from($this->name, new Zend_Db_Expr('DESCRICAO_1 AS regiao, DESCRICAO_2 AS uf, valor'), 'BI_VALE_CULTURA');
        
        if ($regiao) {

            $select->where('DESCRICAO_1 = ?', $regiao);
        }
        if ($uf) {
            $select->where('DESCRICAO_2 = ?', $uf);
        }        
        
        return $this->getTable()->fetchAll($select)->toArray();
    }    
}