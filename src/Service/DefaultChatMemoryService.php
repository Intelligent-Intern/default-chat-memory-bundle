<?php

namespace IntelligentIntern\DefaultChatMemoryBundle\Service;

use App\Contract\ChatHistoryInterface;
use App\Contract\ChatMessageEntryInterface;
use App\Entity\ChatHistory;
use App\Factory\LogServiceFactory;
use App\Repository\ChatHistoryRepository;
use App\Contract\ChatMemoryServiceInterface;
use App\Contract\LogServiceInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DefaultChatMemoryService implements ChatMemoryServiceInterface
{
    private LogServiceInterface $logger;
    private ?int $threadId = null;

    private array $chatMessageEntries;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __construct(
        private readonly LogServiceFactory $logServiceFactory,
        private readonly ChatHistoryRepository $chatHistoryRepository
    ) {
        $this->logger = $this->logServiceFactory->create();
        $this->logger->info('Initialized DefaultChatMemoryService');
    }

    public function supports(string $provider): bool
    {
        return strtolower($provider) === 'default';
    }

    /**
     * @return ChatHistoryInterface
     */
    public function getChatHistory(): ChatHistoryInterface
    {
        if (null !== $this->threadId) {
            $chatHistory = $this->chatHistoryRepository->find($this->threadId);
        } else {
            $chatHistory = new ChatHistory();
        }
        foreach($this->chatMessageEntries as $chatMessageEntry) {
            $chatHistory->addChatMessageEntry($chatMessageEntry);
        }

        return $chatHistory;
    }

    /**
     * @param string $threadId
     * @return self
     */
    public function setThreadId(string $threadId): ChatMemoryServiceInterface
    {
        $this->threadId = $threadId;
        return $this;
    }

    /**
     * @param ChatMessageEntryInterface $chatMessageEntry
     * @return self
     */
    public function addMessageEntry(ChatMessageEntryInterface $chatMessageEntry): self
    {
        $this->chatMessageEntries[] = $chatMessageEntry;
        return $this;
    }
}


