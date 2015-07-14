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

require_once __DIR__ . '/vendor/autoload.php';

try
{
    spl_autoload_register(function($class) {
        $class = str_replace('\\', '/', __DIR__ . '/'.$class.'.php');

        if( is_file( $class ) && file_exists($class) )
        {
            require_once $class;
        }

    }, true);
}
catch(Exception $ex)
{
    echo $ex->getMessage();
    exit;
}
