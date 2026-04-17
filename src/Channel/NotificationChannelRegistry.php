<?php
namespace App\Channel;

final class NotificationChannelRegistry
{
    private array $channelsByName = [];

    public function __construct(iterable $channels = [])
    {
        foreach ($channels as $channel) {
            $this->add($channel);
        }
    }

    /**
     * Add $channel and set internally channelsByName
     * @param NotificationChannelInterface $channel
     * @return void
     */
    public function add(NotificationChannelInterface $channel): void
    {
        $name = $channel->getName();

        if ($name === '') {
            throw new \InvalidArgumentException(sprintf(
                'Notification channel "%s" returned an empty name.',
                $channel::class
            ));
        }

        if (isset($this->channelsByName[$name])) {
            throw new \LogicException(sprintf(
                'Duplicate notification channel name "%s". Services: "%s" and "%s".',
                $name,
                $this->channelsByName[$name]::class,
                $channel::class
            ));
        }

        $this->channelsByName[$name] = $channel;
    }

    public function has(string $name): bool
    {
        return isset($this->channelsByName[$name]);
    }

    /**
     * Return channelsByName if given name exists otherwise throw an InvalidArgumentException
     * @param string $name
     * @return NotificationChannelInterface
     */
    public function get(string $name): NotificationChannelInterface
    {
        if (!isset($this->channelsByName[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown notification channel "%s". Available: [%s]',
                $name,
                implode(', ', $this->getNames())
            ));
        }

        return $this->channelsByName[$name];
    }

    /**
     * Return an array of sorted channel names
     * @return array
     */
    public function getNames(): array
    {
        $names = array_keys($this->channelsByName);
        sort($names);

        return $names;
    }

    public function all(): array
    {
        return $this->channelsByName;
    }
}
