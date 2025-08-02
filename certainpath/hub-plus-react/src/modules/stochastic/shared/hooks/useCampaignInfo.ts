import { useState, useEffect } from "react";
import { Campaign } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaign/types";
import { fetchCampaign } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaign/fetchCampaignApi";

export function useCampaignInfo(campaignId: number) {
  const [campaign, setCampaign] = useState<Campaign | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!campaignId) return;

    setLoading(true);
    setError(null);

    fetchCampaign(campaignId)
      .then((resp) => {
        setCampaign(resp.data);
      })
      .catch((err) => {
        console.error("Error fetching campaign:", err);
        setError(err instanceof Error ? err.message : String(err));
      })
      .finally(() => setLoading(false));
  }, [campaignId]);

  return { campaign, loading, error };
}
