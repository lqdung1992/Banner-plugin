<?php
/**
 * Created by lqdung1992.
 * Date: 3/1/2018
 * Time: 12:54 PM
 */
namespace Plugin\BannerSimple\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Banner
 *
 * @ORM\Table(name="plg_banner_simple")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\BannerSimple\Repository\BannerRepository")
 */
class Banner extends \Eccube\Entity\AbstractEntity
{
    const BANNER = 1;
    const SLIDER = 2;

    const IS_BIG = 2;
    const IS_SMALL = 1;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFileName();
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=false)
     */
    private $file_name;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_no", type="integer", nullable=false)
     */
    private $sort_no;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint", nullable=false, options={"comment":"banner or slider"})
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_big", type="smallint", nullable=true, options={"comment":"is big banner or not"})
     */
    private $big;

    /**
     * @var string
     *
     * @ORM\Column(name="link_to", type="string", length=2000, nullable=true)
     */
    private $link;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_target_blank", type="boolean", nullable=true)
     */
    private $target;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @var \Eccube\Entity\Member
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     * })
     */
    private $Creator;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set file_name
     *
     * @param string $fileName
     * @return Banner
     */
    public function setFileName($fileName)
    {
        $this->file_name = $fileName;

        return $this;
    }

    /**
     * Get file_name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * Set sort_no
     *
     * @param integer $sort_no
     * @return Banner
     */
    public function setSortno($sort_no)
    {
        $this->sort_no = $sort_no;

        return $this;
    }

    /**
     * Get sort_no
     *
     * @return integer
     */
    public function getSortno()
    {
        return $this->sort_no;
    }

    /**
     * Set $Link
     *
     * @param string $Link
     * @return Banner
     */
    public function setLink($Link)
    {
        $this->link = $Link;

        return $this;
    }

    /**
     * Get file_name
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set sort_no
     *
     * @param integer $Type
     * @return Banner
     */
    public function setType($Type)
    {
        $this->type = $Type;

        return $this;
    }

    /**
     * Get Type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sort_no
     *
     * @param integer $Type
     * @return Banner
     */
    public function setBig($Type)
    {
        $this->big = $Type;

        return $this;
    }

    /**
     * Get Type
     *
     * @return integer
     */
    public function getBig()
    {
        return $this->big;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return Banner
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Get target
     *
     * @return integer
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set target
     *
     * @param string $target
     * @return Banner
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate(): \DateTime
    {
        return $this->update_date;
    }

    /**
     * @param \DateTime $update_date
     * @return $this
     */
    public function setUpdateDate(\DateTime $update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }

    /**
     * @return \Eccube\Entity\Member
     */
    public function getCreator()
    {
        return $this->Creator;
    }

    /**
     * @param \Eccube\Entity\Member $Creator
     * @return $this
     */
    public function setCreator(\Eccube\Entity\Member $Creator)
    {
        $this->Creator = $Creator;

        return $this;
    }
}
