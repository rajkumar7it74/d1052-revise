
namespace Drupal\remember_me\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\user\Entity\User;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RememberMeSubscriber extends EventSubscriberInterface {
    protected $currentUser;

    public fuction onKernelRequest(RequestEvent $event) {
        if ($this->currentUser->isAuthenticated()) {
            return;
        }
        $request = $event->getRequest();
        $uid = $request->cookies->get('remember_me_id');
        
        if ($uid) {
            $user = User::load($uid);
            user_login_finalize($user);
            $response new RedirectResponse('/');
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents() {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }
}

