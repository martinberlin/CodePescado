<?php
// Example of a Doctrine entity
namespace App\Entity;

use App\Repository\ClaimRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ClaimRepository::class)]
class Claim
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    /**
     * @var int|null will come after authentication with OAUTH or similar method. Could be also converted to uuid
     */
    #[ORM\Column]
    private ?int $customerId = null;

    #[ORM\Column(length: 64)]
    private ?string $contractId = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $status = "submitted"; // Default value

    #[ORM\Column]
    private ?float $totalAmountEuro = null;

    #[ORM\Column(type: "datetime", nullable: false)]
    private \DateTimeInterface $createdAt;

    /**
     * @var Collection<int, ClaimDocument> Doesn't exist anymore for this example
     */
    //[ORM\OneToMany(targetEntity: ClaimDocument::class, mappedBy: 'claimId', orphanRemoval: true)]
    //private Collection $claimDocuments;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): static
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getContractId(): ?string
    {
        return $this->contractId;
    }

    public function setContractId(string $contractId): static
    {
        $this->contractId = $contractId;

        return $this;
    }

    public function getTotalAmountEuro(): ?float
    {
        return $this->totalAmountEuro;
    }

    public function setTotalAmountEuro(float $totalAmountEuro): static
    {
        $this->totalAmountEuro = $totalAmountEuro;

        return $this;
    }



    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): static {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getStatus(): string {
        return $this->status;
    }
    public function setStatus(string $status): static {
        $this->status = $status;
        return $this;
    }
}
