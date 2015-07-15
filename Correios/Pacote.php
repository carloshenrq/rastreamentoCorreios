<?php
/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Correios;

use WebRequest;

/**
 * Classe para localização do pacote.
 *
 * @final
 */
final class Pacote
{
    /**
     * Código de rastreamento do objeto.
     *
     * @var string
     */
    private $idObjeto;

    /**
     * Identificação do idioma para busca.
     *
     * @var string
     */
    private $idioma;

    /**
     * Construtor para a classe do pacote.
     *
     * @param string $idObjeto Código identificador do objeto.
     * @param string $idioma Idioma para retorno dos dados. '001' => PT_BR, '002' => 'EN_US'
     */
    public function __construct($idObjeto, $idioma = '001')
    {
        // Verifica se o código do pacote está no padrão para teste de rastreamento.
        if(!preg_match(Pacote::REGEX_IDOBJETO, $idObjeto))
        {
            throw new \Exception('Código do pacote não está no formato correto.');
        }
        $this->idObjeto = $idObjeto;
        $this->idioma = $idioma;
    }

    /**
     * Rastreia o objeto enviando um POST para o webservice do correios.
     *
     * @return object Retorna um JSON com os dados de rastreamento.
     */
    public function rastrear()
    {
        $objRetorno = $this->tratarHTML2ARRAY(WebRequest\HttpRequest::createInstance()->post(self::HTTP_REQUEST, [
            'P_ITEMCODE' => '',
            'P_LINGUA' => $this->getIdioma(),
            'P_TESTE' => '',
            'P_TIPO' => '001',
            'P_COD_UNI' => $this->getIdObjeto(),
            'Z_ACTION' => 'Search'
        ], 'text'));

        return json_decode(json_encode([
            'idObjeto' => $this->getIdObjeto(),
            'rastreamento' => $objRetorno
        ]));
    }

    /**
     * Trata o retorno do HTML dos correios para JSON.
     *
     * @param string $sHTML HTML dos correios para retorno dos dados.
     *
     * @return object Objeto de retorno.
     */
    private function tratarHTML2ARRAY($sHTML)
    {
        $iLength = strpos($sHTML, '<table  border cellpadding=1 hspace=10>');
        // Caso não localize a string, significa que retornou erro.
        if($iLength === false)
        {
            return [];
        }
        // Realiza alguns tratamentos para deixar somente os <tr> e <td>
        //  para leitura do DOMDocument.
        $sHTML = substr($sHTML, $iLength);
        $iLength = strpos($sHTML, '</TABLE>');
        $sHTML = substr($sHTML, 0, $iLength);
        $sHTML = strip_tags(substr($sHTML, strpos($sHTML, '<tr>')), '<tr><td>');
        $sHTML = str_replace("\n", "", trim($sHTML));

        // Inicializa o DOMDocument e carrega o html para leitura.
        $dom = new \DOMDocument();
        $dom->loadHTML($sHTML);

        $c = 0; // Posição dos dados para gerar o json.
        $data = []; // Dados que serão transformados no JSON.
        $ultimo = '';
        $ultimoTamanho = 0;

        // Varre todos os TR encontrados
        foreach($dom->getElementsByTagName('tr') as $i => $row)
        {
            // Primeira linha é cabeçalho de descrição, então pula.
            if($i == 0)
                continue;

            // Obtém todas as colunas da linha atual.
            $columns = $row->getElementsByTagName('td');

            // Caso esteja com 3 colunas, significa que não existe ação.
            // Pula para o próximo.
            if($columns->length == 3 && $i > 1)
                $c++;

            // Caso não definido os dados para a posição, inicializa o vetor.
            if(!isset($data[$c]))
                $data[$c] = [
                                'data' => '',
                                'local' => '',
                                'info' => '',
                                'acao' => ''
                            ];

            // Linha impar: 3 colunas
            // Linha par  : 1 coluna
            if($columns->length > 1)
            {
                $data[$c]['data'] = $columns->item(0)->nodeValue;
                $data[$c]['local'] = $columns->item(1)->nodeValue;
                $data[$c]['info'] = $columns->item(2)->nodeValue;
            }
            else
            {
                $data[$c]['acao'] = $columns->item(0)->nodeValue;
            }
        }
        // Converte o array para objeto json.
        return $data;
    }

    /**
     * Obtém o código do objeto que foi instânciado.
     *
     * @return string
     */
    public function getIdObjeto()
    {
        return $this->idObjeto;
    }

    /**
     * Obtém o idioma informado no construtor.
     *
     * @return string
     */
    public function getIdioma()
    {
        return $this->idioma;
    }

    /**
     * Expressão regular para validar o parametro para o código de objeto a ser rastreado.
     *
     * @const string
     */
    const REGEX_IDOBJETO = '/^([A-Z]{2})([0-9]{9})([A-Z]{2})$/i';

    /**
     * Caminho padrão para rastreamento dos objetos do correio.
     *
     * @const string
     */
    const HTTP_REQUEST = 'http://websro.correios.com.br/sro_bin/txect01$.QueryList';
}
