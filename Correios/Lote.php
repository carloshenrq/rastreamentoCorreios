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
 * Classe para localização de pacotes por lote.
 *
 * @final
 */
final class Lote
{
    /**
     * Array contendo todos os Ids de pacote para realizar a busca.
     * @var array
     */
    private $idPacote;

    /**
     * Idioma para retorno dos dados. '001' => PT_BR, '002' => 'EN_US'
     * @var string
     */
    private $idioma;

    /**
     * Construtor para a pesquisa de objetos em lote.
     *
     * @param array $idPacote Array contendo todos os Ids de pacote para realizar a busca.
     * @param string $idioma Idioma para retorno dos dados. '001' => PT_BR, '002' => 'EN_US'
     */
    public function __construct($idPacote = [], $idioma = '001')
    {
        if(sizeof($idPacote) == 0)
        {
            throw new \Exception('Nenhum item informado para realizar a busca em lote.');
        }

        $this->idPacote = $idPacote;
        $this->idioma = $idioma;
    }

    /**
     * Rastreia os vários objetos informados para a pesquisa em lote e os retorna.
     *
     * @return mixed
     */
    public function rastrear()
    {
        $rastreamento = [];
        foreach($this->idPacote as $pacote)
        {
            $obj = new Pacote($pacote, $this->idioma);
            $rastreamento[] = $obj->rastrear();
        }
        return json_decode(json_encode($rastreamento));
    }
}
