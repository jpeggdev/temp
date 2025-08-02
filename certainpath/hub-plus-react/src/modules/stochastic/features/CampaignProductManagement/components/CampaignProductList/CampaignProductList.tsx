import React, { useState, useMemo, useCallback } from "react";
import { useDispatch } from "react-redux";
import CampaignProductListFilters from "../CampaignProductListFilters/CampaignProductListFilters";
import DataTable from "../../../../../../components/Datatable/Datatable";
import MainPageWrapper from "../../../../../../components/MainPageWrapper/MainPageWrapper";
import { CampaignProduct } from "@/modules/stochastic/features/CampaignManagement/api/fetchCampaignProducts/types";
import { createCampaignProductsColumns } from "../CampaignProductColumns/CampaignProductsColumns";
import { useCampaignProducts } from "../../hooks/useCampaignProducts";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";
import CampaignProductDialog from "../CampaignProductDialog/CampaignProductDialog";
import DeleteConfirmationDialog from "../DeleteConfirmationDialog/DeleteConfirmationDialog";
import { setCurrentProduct } from "../../slices/campaignProductsSlice";

const CampaignProductList: React.FC = () => {
  const dispatch = useDispatch();
  const [refreshCounter, setRefreshCounter] = useState(0);
  const {
    campaignProducts,
    totalCount,
    loading,
    error,
    pagination,
    filters,
    handlePaginationChange,
    handleFilterChange,
  } = useCampaignProducts(refreshCounter);

  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [deleteDialogState, setDeleteDialogState] = useState({
    isOpen: false,
    productId: null as string | number | null,
    productName: "",
  });

  const refreshList = useCallback(() => {
    setRefreshCounter((prev) => prev + 1);
  }, []);

  const handleCreateProduct = () => {
    dispatch(setCurrentProduct(null));
    setIsDialogOpen(true);
  };

  const handleEditProduct = (product: CampaignProduct) => {
    dispatch(setCurrentProduct(product));
    setIsDialogOpen(true);
  };

  const handleDeleteProduct = (product: CampaignProduct) => {
    setDeleteDialogState({
      isOpen: true,
      productId: product.id,
      productName: product.name || "this campaign product",
    });
  };

  const columns = useMemo(
    () => createCampaignProductsColumns(handleEditProduct, handleDeleteProduct),
    [],
  );

  return (
    <MainPageWrapper error={error} title="Campaign Products">
      <div className="flex justify-between items-center mb-4">
        <CampaignProductListFilters
          filters={filters}
          onFilterChange={handleFilterChange}
        />
        <Button
          className="flex items-center gap-1"
          onClick={handleCreateProduct}
          size="sm"
        >
          <Plus className="w-4 h-4" />
          Add Product
        </Button>
      </div>
      <div className="relative">
        <DataTable<CampaignProduct>
          columns={columns}
          data={campaignProducts}
          error={error}
          loading={loading}
          noDataMessage="No products found"
          onPageChange={(newPageIndex, newPageSize) =>
            handlePaginationChange({
              pageIndex: newPageIndex,
              pageSize: newPageSize,
            })
          }
          pageIndex={pagination.pageIndex}
          pageSize={pagination.pageSize}
          rowKeyExtractor={(item) => item.id}
          totalCount={totalCount}
        />
      </div>

      <CampaignProductDialog
        isOpen={isDialogOpen}
        onClose={() => setIsDialogOpen(false)}
        onSuccess={refreshList}
      />

      <DeleteConfirmationDialog
        isOpen={deleteDialogState.isOpen}
        onClose={() =>
          setDeleteDialogState({
            isOpen: false,
            productId: null,
            productName: "",
          })
        }
        onSuccess={refreshList}
        productId={deleteDialogState.productId}
        productName={deleteDialogState.productName}
      />
    </MainPageWrapper>
  );
};

export default CampaignProductList;
