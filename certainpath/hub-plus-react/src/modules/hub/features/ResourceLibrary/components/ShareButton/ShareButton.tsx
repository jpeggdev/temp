import { Share2, Link, Twitter, Facebook, Linkedin, Mail } from "lucide-react";
import { useState, useEffect } from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { toast } from "react-toastify";

interface ShareButtonProps {
  title: string;
  description?: string;
  url: string;
  className?: string;
}

export function ShareButton({
  title,
  description,
  url,
  className,
}: ShareButtonProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [fullUrl, setFullUrl] = useState(url);
  const [hasShareCapability, setHasShareCapability] = useState(false);

  useEffect(() => {
    if (typeof window !== "undefined") {
      setFullUrl(`${window.location.origin}${url}`);
      setHasShareCapability("share" in navigator);
    }
  }, [url]);

  const shareData = {
    title,
    text: description,
    url: fullUrl,
  };

  const handleShare = async () => {
    if (typeof window !== "undefined" && hasShareCapability) {
      try {
        await navigator.share(shareData);
      } catch (error) {
        if ((error as Error).name !== "AbortError") {
          console.error("Error sharing:", error);
        }
      }
    }
  };

  const copyToClipboard = () => {
    if (typeof window !== "undefined") {
      navigator.clipboard.writeText(fullUrl).then(() => {
        toast.success("Link copied to clipboard");
        setIsOpen(false);
      });
    }
  };

  const shareLinks = {
    twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(fullUrl)}`,
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(fullUrl)}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(fullUrl)}`,
    email: `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(fullUrl)}`,
  };

  return (
    <DropdownMenu onOpenChange={setIsOpen} open={isOpen}>
      <DropdownMenuTrigger asChild>
        <Button
          className={`h-8 w-8 p-0 ${className}`}
          onClick={() => {
            if (hasShareCapability) {
              handleShare();
            }
          }}
          size="sm"
          variant="ghost"
        >
          <Share2 className="h-4 w-4" />
          <span className="sr-only">Share</span>
        </Button>
      </DropdownMenuTrigger>
      {!hasShareCapability && (
        <DropdownMenuContent align="end">
          <DropdownMenuItem className="gap-2" onClick={copyToClipboard}>
            <Link className="h-4 w-4" />
            Copy link
          </DropdownMenuItem>
          <DropdownMenuItem asChild className="gap-2">
            <a
              href={shareLinks.twitter}
              rel="noopener noreferrer"
              target="_blank"
            >
              <Twitter className="h-4 w-4" />
              Twitter
            </a>
          </DropdownMenuItem>
          <DropdownMenuItem asChild className="gap-2">
            <a
              href={shareLinks.facebook}
              rel="noopener noreferrer"
              target="_blank"
            >
              <Facebook className="h-4 w-4" />
              Facebook
            </a>
          </DropdownMenuItem>
          <DropdownMenuItem asChild className="gap-2">
            <a
              href={shareLinks.linkedin}
              rel="noopener noreferrer"
              target="_blank"
            >
              <Linkedin className="h-4 w-4" />
              LinkedIn
            </a>
          </DropdownMenuItem>
          <DropdownMenuItem asChild className="gap-2">
            <a href={shareLinks.email}>
              <Mail className="h-4 w-4" />
              Email
            </a>
          </DropdownMenuItem>
        </DropdownMenuContent>
      )}
    </DropdownMenu>
  );
}
